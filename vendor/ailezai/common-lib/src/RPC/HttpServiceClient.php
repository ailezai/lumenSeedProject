<?php

namespace AiLeZai\Common\Lib\RPC;

use AiLeZai\Common\Lib\Common\ScopeExit;
use AiLeZai\Common\Lib\Config\CFG;
use AiLeZai\Common\Lib\Jwt\Api\JwtHelper;
use AiLeZai\Common\Lib\Log\LOG;
use GuzzleHttp\Client;
use AiLeZai\Common\Lib\Log\ReqRespLogBuilder;
use AiLeZai\Common\Lib\Prometheus\BackEndService\RPCHistogram;

class HttpServiceClient
{
    /**
     * @param string $conf_key 服务配置key名
     * @param bool $force_new 是否强制新建$client对象
     * @return Client
     */
    private static function getJavaProxyClient($conf_key, $force_new = false)
    {
        static $instanceArr = array();

        // 使用静态变量, 同一请求上下文 复用$client对象
        if ($force_new || empty($instanceArr[$conf_key])) {
            $instanceArr[$conf_key] = new Client(array(
                'base_uri' => CFG::get("/java-proxy/$conf_key/host"),
                'connect_timeout' => CFG::get("/java-proxy/$conf_key/connect_timeout", 1.0),
                'timeout' => CFG::get("/java-proxy/$conf_key/timeout", 3.0),
                'http_errors' => false, // 4xx,5xx时不抛异常
            ));
        }

        return $instanceArr[$conf_key];
    }

    /**
     * @return array 调用java-proxy的请求头
     */
    private static function buildJavaProxyReqHeaders()
    {
        // 默认一定包含 traceId
        $headers = array(
            'X-B3-TraceId' => LOG::getTraceId()
        );

        // 若有jwt头, 透传到底层服务
        JwtHelper::addPassThroughAuthHeader($headers);

        return $headers;
    }

    /**
     * @param $output string HTTP响应内容
     * @return mixed 业务成功, 返回ResponseVo中的`result`
     * @throws ResponseVoException 业务失败, 将错误信息包装成异常往上传递
     * @throws \Exception 接口响应的格式异常 (配置错误? 底层路由错误? 底层非业务异常?)
     */
    private static function parseJavaProxyResponseVo($output)
    {
        $responseVo = json_decode($output, true);

        // 统一判断java-proxy的错误码
        if (!is_array($responseVo) || !isset($responseVo['resultCode'])) {
            // 如果是Spring Boot框架默认错误结构, 将`status`提取出来作为errcode
            // {"timestamp":xxx,"status":404,"error":"Not Found","message":"No message available","path":"xxx"}
            if (isset($responseVo['status'])) {
                RPCHistogram::get()->l_errcode($responseVo['status']);
            }

            throw new \Exception(sprintf("O=%s format invalid!", $output));
        } else {
            // 避免PHP Notice
            $responseVo += array(
                'errorCode' => null,
                'errorDesc' => null,
                'result' => null,
                'token' => null,
            );

            // 将底层服务响应的token 更新到当前上下文.
            JwtHelper::updateToken($responseVo['token']);

            // 如果是$responseVo结构, 将`errorCode`提取出来作为errcode (<空> => 0表示成功)
            $errcode = empty($responseVo['errorCode']) ? 0 : $responseVo['errorCode'];
            RPCHistogram::get()->l_errcode($errcode);

            if ($responseVo['resultCode'] != 'SUCCESS') {
                throw new ResponseVoException($responseVo);
            } else {
                return $responseVo['result'];
            }
        }
    }

    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     *
     * @return mixed 请求成功后$responseVo中的result
     *
     * @throws ResponseVoException 业务逻辑错误
     * @throws \Exception 网络错误或路由错误
     */
    public static function callRedPack($url_path, $params)
    {
        $redPackClient = self::getJavaProxyClient('red_pack');

        $req_resp_log = new ReqRespLogBuilder('red_pack', 'red_pack:java-proxy', $url_path, $params);

        RPCHistogram::get()->l_service('red_pack')->l_if($url_path);
        $__fn_stat = new ScopeExit(function () use ($req_resp_log) {
            RPCHistogram::get()->stat($req_resp_log->durationSec * 1000);
        });

        try {

            $output = $redPackClient
                ->request('POST', $url_path, array(
                    'json' => $params,
                    'headers' => self::buildJavaProxyReqHeaders(),
                ))
                ->getBody()
                ->getContents();

            $req_resp_log->respOk($output);
        } catch (\Exception $e) {
            $req_resp_log->respError($e);
            RPCHistogram::get()->parsePHPException($e);
            throw $e;
        }

        return self::parseJavaProxyResponseVo($output);
    }

    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     *
     * @return mixed 请求成功后$responseVo中的result
     *
     * @throws ResponseVoException 业务逻辑错误
     * @throws \Exception 网络错误或路由错误
     */
    public static function callAxUser($url_path, $params)
    {
        $axUserClient = self::getJavaProxyClient('ax_user');

        $req_resp_log = new ReqRespLogBuilder('ax_user', 'ax_user:java-proxy', $url_path, $params);

        RPCHistogram::get()->l_service('ax_user')->l_if($url_path);
        $__fn_stat = new ScopeExit(function () use ($req_resp_log) {
            RPCHistogram::get()->stat($req_resp_log->durationSec * 1000);
        });

        try {

            $output = $axUserClient
                ->request('POST', $url_path, array(
                    'json' => $params,
                    'headers' => self::buildJavaProxyReqHeaders(),
                ))
                ->getBody()
                ->getContents();

            $req_resp_log->respOk($output);
        } catch (\Exception $e) {
            $req_resp_log->respError($e);
            RPCHistogram::get()->parsePHPException($e);
            throw $e;
        }

        return self::parseJavaProxyResponseVo($output);
    }

    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     * @param string $method 请求方式
     * @param string $contentType 接口路径
     *
     * @return mixed 请求成功后$responseVo中的result
     *
     * @throws ResponseVoException 业务逻辑错误
     * @throws \Exception 网络错误或路由错误
     */
    public static function callDataMarket($url_path, $params, $method = 'POST', $contentType = 'json')
    {
        $dataMarketClient = self::getJavaProxyClient('data_market');

        $req_resp_log = new ReqRespLogBuilder('data_market', 'data_market:java-proxy', $url_path, $params);

        RPCHistogram::get()->l_service('data_market')->l_if($url_path);
        $__fn_stat = new ScopeExit(function () use ($req_resp_log) {
            RPCHistogram::get()->stat($req_resp_log->durationSec * 1000);
        });

        try {

            $output = $dataMarketClient
                ->request($method, $url_path, array(
                    $contentType => $params,
                    'headers' => self::buildJavaProxyReqHeaders(),
                ))
                ->getBody()
                ->getContents();

            $req_resp_log->respOk($output);
        } catch (\Exception $e) {
            $req_resp_log->respError($e);
            RPCHistogram::get()->parsePHPException($e);
            throw $e;
        }

        return self::parseJavaProxyResponseVo($output);
    }

    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     *
     * @return mixed 请求成功后$responseVo中的result
     *
     * @throws ResponseVoException 业务逻辑错误
     * @throws \Exception 网络错误或路由错误
     */
    public static function callLabel($url_path, $params)
    {
        $labelClient = self::getJavaProxyClient('label');

        $req_resp_log = new ReqRespLogBuilder('label', 'label:java-proxy', $url_path, $params);

        RPCHistogram::get()->l_service('label')->l_if($url_path);
        $__fn_stat = new ScopeExit(function () use ($req_resp_log) {
            RPCHistogram::get()->stat($req_resp_log->durationSec * 1000);
        });

        try {

            $output = $labelClient
                ->request('POST', $url_path, array(
                    'form_params' => $params,
                    'headers' => self::buildJavaProxyReqHeaders(),
                ))
                ->getBody()
                ->getContents();

            $req_resp_log->respOk($output);
        } catch (\Exception $e) {
            $req_resp_log->respError($e);
            RPCHistogram::get()->parsePHPException($e);
            throw $e;
        }

        return self::parseJavaProxyResponseVo($output);
    }

    /**
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     *
     * @return mixed 请求成功后$responseVo中的result
     *
     * @throws ResponseVoException 业务逻辑错误
     * @throws \Exception 网络错误或路由错误
     */
    public static function callPush($url_path, $params)
    {
        $pushClient = self::getJavaProxyClient('push');

        $req_resp_log = new ReqRespLogBuilder('push', 'push:java-proxy', $url_path, $params);

        RPCHistogram::get()->l_service('push')->l_if($url_path);
        $__fn_stat = new ScopeExit(function () use ($req_resp_log) {
            RPCHistogram::get()->stat($req_resp_log->durationSec * 1000);
        });

        try {

            $output = $pushClient
                ->request('POST', $url_path, array(
                    'json' => $params,
                    'headers' => self::buildJavaProxyReqHeaders(),
                ))
                ->getBody()
                ->getContents();

            $req_resp_log->respOk($output);
        } catch (\Exception $e) {
            $req_resp_log->respError($e);
            RPCHistogram::get()->parsePHPException($e);
            throw $e;
        }

        return self::parseJavaProxyResponseVo($output);
    }

    /**
     * 招聘结算（返费）系统
     * @param string $url_path 接口路径
     * @param array $params 接口参数kv数组
     * @param integer $adminUserId 操作者ID
     * @param string $method 请求方式
     * @param string $contentType 接口路径
     *
     * @return mixed 请求成功后$responseVo中的result
     *
     * @throws ResponseVoException 业务逻辑错误
     * @throws \Exception 网络错误或路由错误
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function callZpSettle($url_path, $params, $adminUserId, $method = 'POST', $contentType = 'json')
    {
        $dataMarketClient = self::getJavaProxyClient('zp_settle');

        $req_resp_log = new ReqRespLogBuilder('zp_settle', 'zp_settle:java-proxy', $url_path, $params);

        RPCHistogram::get()->l_service('zp_settle')->l_if($url_path);
        $__fn_stat = new ScopeExit(function () use ($req_resp_log) {
            RPCHistogram::get()->stat($req_resp_log->durationSec * 1000);
        });

        try {
            $headers = self::buildJavaProxyReqHeaders();
            $headers['ADMIN_USER_ID'] = $adminUserId;
            $output = $dataMarketClient
                ->request($method, $url_path, array(
                    $contentType => $params,
                    'headers' => $headers,
                ))
                ->getBody()
                ->getContents();

            $req_resp_log->respOk($output);
        } catch (\Exception $e) {
            $req_resp_log->respError($e);
            RPCHistogram::get()->parsePHPException($e);
            throw $e;
        }

        return self::parseJavaProxyResponseVo($output);
    }

}
