<?php
/**
 * Created by PhpStorm.
 * User: sangechen
 * Date: Jun-11
 * Time: 13:33
 */

namespace AiLeZai\Common\Lib\RPC;

use AiLeZai\Common\Lib\Common\IpUtil;
use AiLeZai\Common\Lib\Common\ScopeExit;
use AiLeZai\Common\Lib\Config\CFG;
use AiLeZai\Common\Lib\Jwt\Api\JwtHelper;
use AiLeZai\Common\Lib\Log\LOG;
use GuzzleHttp\Client;
use AiLeZai\Common\Lib\Log\ReqRespLogBuilder;
use AiLeZai\Common\Lib\Prometheus\BackEndService\RPCHistogram;

class HaoApiClient
{
    /**
     * 兼容处理原有haoapi配置的host没有 `http:// ` 前缀问题
     * @param string $conf_key
     * @return string
     */
    private static function _get_base_uri($conf_key)
    {
        $base_uri = CFG::get("/haoapi/$conf_key/host");
        if (preg_match('/^http[s]?:\/\//', $base_uri) !== 1) {
            $base_uri = 'http://' . $base_uri;
        }
        return $base_uri;
    }

    /**
     * @param string $conf_key 服务配置key名
     * @param bool $force_new 是否强制新建$client对象
     * @return Client
     */
    private static function getHttpClient($conf_key, $force_new = false)
    {
        static $instanceArr = array();

        // 使用静态变量, 同一请求上下文 复用$client对象
        if ($force_new || empty($instanceArr[$conf_key])) {
            $instanceArr[$conf_key] = new Client(array(
                'base_uri' => self::_get_base_uri($conf_key),
                'connect_timeout' => CFG::get("/haoapi/$conf_key/connect_timeout", 1.0),
                'timeout' => CFG::get("/haoapi/$conf_key/timeout", 3.0),
                'http_errors' => false, // 4xx,5xx时不抛异常
            ));
        }

        return $instanceArr[$conf_key];
    }

    /**
     * @param string $conf_key 服务配置key名
     * @param string $url_path 请求的接口, 格式: `/xxx/yyy`
     * @return string
     * @throws \Exception 拼接后parse_url()结果为false
     */
    private static function buildSignLink($conf_key, $url_path)
    {
        $url = self::_get_base_uri($conf_key) . '/' . $url_path;
        $parts = parse_url($url);
        if (empty($parts) || empty($parts['host']) || empty($parts['path'])) {
            throw new \Exception("invalid url[" . $url . "]!");
        }

        $sign_link = $parts['host'];
        if (!empty($parts['port'])) {
            $sign_link .= ':' . $parts['port'];
        }
        $sign_link .= '/' . trim($parts['path'], '/'); //去掉多余的`/`

        return $sign_link;
    }

    /**
     * @param string $conf_key 服务配置key名
     * @param string $url_path 请求的接口, 格式: `/xxx/yyy`
     * @param string[] $params 请求参数
     * @param array|HaoApi\HaoReqHeaders $haoReqHeaders 请求头封装对象
     * @return array 返回算出签名后的头信息
     * @throws \Exception 拼接后parse_url()结果为false
     */
    private static function buildHaoApiReqHeadersWithSign($conf_key, $url_path, $params, $haoReqHeaders)
    {
        if (is_array($haoReqHeaders)) {
            $haoReqHeaders = new HaoApi\HaoReqHeaders($haoReqHeaders);
        } elseif (!($haoReqHeaders instanceof HaoApi\HaoReqHeaders)) {
            $haoReqHeaders = new HaoApi\HaoReqHeaders();
        } // else 已经是 HaoApi\HaoReqHeaders 对象

        // 默认一定包含 traceId
        $headers = array(
            'X-B3-TraceId' => LOG::getTraceId()
        );
        // 若有jwt头, 透传到底层服务
        JwtHelper::addPassThroughAuthHeader($headers);
        // 若有客户端ip, 透传到底层服务
        $clientIp = IpUtil::getCurrentIP();
        if (!empty($clientIp) && $clientIp != 'unknown') {
            $headers['CLIENT_IP'] = $clientIp;
        }
        $haoReqHeaders->assign($headers);

        $sign_link = self::buildSignLink($conf_key, $url_path);

        $dtSecretMap = CFG::get("/haoapi/$conf_key/dt_secret_config", array());
        $deviceType = CFG::get("/haoapi/$conf_key/device_type", 2);
        $dtSecret = CFG::get("/haoapi/$conf_key/dt_secret");
        if (!empty($dtSecret)) {
            $haoReqHeaders->Devicetype($deviceType);
            $dtSecretMap[$deviceType] = $dtSecret;
        }

        return $haoReqHeaders->buildSign($sign_link, $params, $dtSecretMap);
    }

    /**
     * @var HaoApi\HaoResult 静态变量,保存最后一次调用的HaoResult
     */
    public static $lastHaoResult;

    /**
     * @param $output string HTTP响应内容
     * @return mixed 业务成功, 返回HaoResult中的`results`
     * @throws \Exception 业务失败, 将错误信息包装成异常往上传递
     *                    接口响应的格式异常 (配置错误? 底层路由错误? 底层非业务异常?)
     */
    private static function parseHaoResult($output)
    {
        $haoResultArr = json_decode($output, true);

        // 统一判断 HaoApi 的错误码
        if (!is_array($haoResultArr) || !isset($haoResultArr['errorCode'])) {
            throw new \Exception(sprintf("O=%s format invalid!", $output));
        } else {
            self::$lastHaoResult = new HaoApi\HaoResult($haoResultArr);

            // 将底层服务响应的token 更新到当前上下文.
            JwtHelper::updateToken(self::$lastHaoResult->token);

            // 将`errorCode`提取出来作为errcode (0表示成功, <空> => -1)
            RPCHistogram::get()->l_errcode(self::$lastHaoResult->errorCode);

            if (self::$lastHaoResult->errorCode !== 0) {
                throw new HaoResultException(self::$lastHaoResult);
            } else {
                return self::$lastHaoResult->results;
            }
        }
    }


    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     * @param array|HaoApi\HaoReqHeaders $haoReqHeaders 请求头封装对象
     *
     * @return mixed 请求成功后HaoResult中的`results`
     *
     * @throws HaoResultException 业务逻辑错误
     * @throws \Exception 拼接后parse_url()结果为false 或者 网络异常
     */
    public static function callJjbApi($url_path, $params, $haoReqHeaders = null)
    {
        self::$lastHaoResult = null;

        $jjbApiClient = self::getHttpClient('jjb');

        $req_resp_log = new ReqRespLogBuilder('jjb', 'jjb:haoapi', $url_path, $params);

        RPCHistogram::get()->l_service('jjbapi')->l_if($url_path);
        $__fn_stat = new ScopeExit(function () use ($req_resp_log) {
            RPCHistogram::get()->stat($req_resp_log->durationSec * 1000);
        });

        try {

            $output = $jjbApiClient
                ->request('POST', $url_path, array(
                    'form_params' => $params,
                    'headers' => self::buildHaoApiReqHeadersWithSign('jjb', $url_path, $params, $haoReqHeaders),
                ))
                ->getBody()
                ->getContents();

            $req_resp_log->respOk($output);
        } catch (\Exception $e) {
            $req_resp_log->respError($e);
            RPCHistogram::get()->parsePHPException($e);
            throw $e;
        }

        return self::parseHaoResult($output);
    }

    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     * @param array|HaoApi\HaoReqHeaders $haoReqHeaders 请求头封装对象
     * @return array 异常返回空数组, 调用成功返回HaoResult数组
     */
    public static function _callJjbApi_nothrow_array($url_path, $params, $haoReqHeaders = null)
    {
        try {
            self::callJjbApi($url_path, $params, $haoReqHeaders);
        } catch (\Exception $e) {
            LOG::error(LOG::e2str($e));
        }

        return (array) self::$lastHaoResult;
    }

    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     * @param array|HaoApi\HaoReqHeaders $haoReqHeaders 请求头封装对象
     *
     * @return mixed 请求成功后HaoResult中的`results`
     *
     * @throws HaoResultException 业务逻辑错误
     * @throws \Exception 拼接后parse_url()结果为false 或者 网络异常
     */
    public static function callDgqApi($url_path, $params, $haoReqHeaders = null)
    {
        self::$lastHaoResult = null;

        $jjbApiClient = self::getHttpClient('dgq');

        $req_resp_log = new ReqRespLogBuilder('dgq', 'dgq:haoapi', $url_path, $params);

        RPCHistogram::get()->l_service('dgqapi')->l_if($url_path);
        $__fn_stat = new ScopeExit(function () use ($req_resp_log) {
            RPCHistogram::get()->stat($req_resp_log->durationSec * 1000);
        });

        try {

            $output = $jjbApiClient
                ->request('POST', $url_path, array(
                    'form_params' => $params,
                    'headers' => self::buildHaoApiReqHeadersWithSign('dgq', $url_path, $params, $haoReqHeaders),
                ))
                ->getBody()
                ->getContents();

            $req_resp_log->respOk($output);
        } catch (\Exception $e) {
            $req_resp_log->respError($e);
            RPCHistogram::get()->parsePHPException($e);
            throw $e;
        }

        return self::parseHaoResult($output);
    }

    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     * @param array|HaoApi\HaoReqHeaders $haoReqHeaders 请求头封装对象
     * @return array 异常返回空数组, 调用成功返回HaoResult数组
     */
    public static function _callDgqApi_nothrow_array($url_path, $params, $haoReqHeaders = null)
    {
        try {
            self::callDgqApi($url_path, $params, $haoReqHeaders);
        } catch (\Exception $e) {
            LOG::error(LOG::e2str($e));
        }

        return (array) self::$lastHaoResult;
    }

    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     * @param array|HaoApi\HaoReqHeaders $haoReqHeaders 请求头封装对象
     *
     * @return mixed 请求成功后HaoResult中的`results`
     *
     * @throws HaoResultException 业务逻辑错误
     * @throws \Exception 拼接后parse_url()结果为false 或者 网络异常
     */
    public static function callDgdApi($url_path, $params, $haoReqHeaders = null)
    {
        self::$lastHaoResult = null;

        $jjbApiClient = self::getHttpClient('dgd');

        $req_resp_log = new ReqRespLogBuilder('dgd', 'dgd:haoapi', $url_path, $params);

        RPCHistogram::get()->l_service('dgdapi')->l_if($url_path);
        $__fn_stat = new ScopeExit(function () use ($req_resp_log) {
            RPCHistogram::get()->stat($req_resp_log->durationSec * 1000);
        });

        try {

            $output = $jjbApiClient
                ->request('POST', $url_path, array(
                    'form_params' => $params,
                    'headers' => self::buildHaoApiReqHeadersWithSign('dgd', $url_path, $params, $haoReqHeaders),
                ))
                ->getBody()
                ->getContents();

            $req_resp_log->respOk($output);
        } catch (\Exception $e) {
            $req_resp_log->respError($e);
            RPCHistogram::get()->parsePHPException($e);
            throw $e;
        }

        return self::parseHaoResult($output);
    }

    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     * @param array|HaoApi\HaoReqHeaders $haoReqHeaders 请求头封装对象
     * @return array 异常返回空数组, 调用成功返回HaoResult数组
     */
    public static function _callDgdApi_nothrow_array($url_path, $params, $haoReqHeaders = null)
    {
        try {
            self::callDgdApi($url_path, $params, $haoReqHeaders);
        } catch (\Exception $e) {
            LOG::error(LOG::e2str($e));
        }

        return (array) self::$lastHaoResult;
    }
}
