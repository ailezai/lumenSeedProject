<?php
namespace AiLeZai\Common\Lib\RPC\HaoApi;

/**
 * Class HaoReqHeaders
 * @package AiLeZai\Common\Lib\RPC\HaoApi
 *
 * @method HaoReqHeaders Clientinfo(string $v)       调用方信息 (依赖于`r`入参)
 * @method HaoReqHeaders Clientversion(string $v)    透传客户端的app版本号
 * @method HaoReqHeaders Devicetype(string $v)       透传客户端的设备类型(会关联签名时的sign)
 * @method HaoReqHeaders Devicetoken(string $v)      透传客户端的device_token
 * @method HaoReqHeaders Userid(string $v)           透传haoapi的9位数字用户id
 * @method HaoReqHeaders Logintime(string $v)        透传haoapi的最后登录时间
 * @method HaoReqHeaders Checkcode(string $v)        透传haoapi的check_code
 * @method HaoReqHeaders Isdebug(string $v)          是否开启调试模式 (依赖于`r`入参)
 * @method HaoReqHeaders Requesttime(string $v)      请求时间戳
 *
 * @method HaoReqHeaders Signature(string $v)        设置签名
 *
 * @method HaoReqHeaders Adminid(string $v)          admin后台的用户id
 * @method HaoReqHeaders Iscw(string $v)             是否来自cw后台
 */
class HaoReqHeaders
{
    /**
     * `$Headername` => `默认值`
     */
    const REQUIRED_HEADER_NAMES = array(
        // 必填Header
        'Clientinfo' => 'HaoApiClient',
        'Clientversion' => '',
        'Devicetype' => 2, //1~browser 2~pc 3~Android 4~iOS 5~Windows
        'Devicetoken' => '',
        'Userid' => 0,
        'Logintime' => 0,
        'Checkcode' => '',
        'Isdebug' => 0,
        'Requesttime' => 0,

        // 计算得到sign
        //'Signature' => '',

        // 可选Header
        //'Adminid' => null,
        //'Iscw' => null,

        // 自动注入的Header
        //'X-B3-TraceId' => null, //traceId
        //'Authorization' => null, //jwt
        //'CLIENT_IP' => null, //客户端IP
    );

    public static function builder($headers = array())
    {
        return new static($headers);
    }

    public function __construct($headers = array())
    {
        $this->headers = self::REQUIRED_HEADER_NAMES;
        $this->assign($headers);
    }

    protected $headers = array();

    /**
     * @param array $headers 批量设置header信息
     * @return static
     */
    public function assign($headers = array())
    {
        foreach ($headers as $k => $v) {
            $this->headers[$k] = strval($v); //将$v转成string类型
        }
        return $this;
    }

    /**
     * 单独更新某个header的值
     * @param string header名
     * @param string[] $arguments [0]对应header的值
     * @return static
     */
    public function __call($name, $arguments)
    {
        $this->headers[$name] = strval($arguments[0]); //将$v转成string类型
        return $this;
    }

    /**
     * @param string $sign_link 例如: jjbapi-dev.ailezai.com:8080/aaa/bbb
     * @param string[] $params 请求参数
     * @param string[] $dtSecretMap 算签名时的secret配置
     * @return array 返回加上签名后的headers数组
     */
    public function buildSign($sign_link, $params, $dtSecretMap)
    {
        $this->Requesttime(time());

        $tmpArr = array();
        // 组装haoapi的headers
        foreach (array_intersect_key($this->headers, self::REQUIRED_HEADER_NAMES) as $k => $v) {
            array_push($tmpArr, sprintf('%s=%s', $k, $v));
        }
        // 组装请求url {服务端用`HTTP_HOST`和去掉参数的`REQUEST_URI`}
        array_push($tmpArr, sprintf('%s=%s', 'link', $sign_link));
        // 组装请求数组
        foreach ($params as $k => $v) {
            array_push($tmpArr, sprintf('%s=%s', $k, $v));
        }
        // 组装secret
        $device_type = intval($this->headers['Devicetype']);
        $dt_secret = isset($dtSecretConfig[$device_type]) ? $dtSecretMap[$device_type] : $dtSecretMap[2];
        array_push($tmpArr, $dt_secret);

        // 对数组进行自然排序
        sort($tmpArr, SORT_STRING);
        // 将排序后的数组组合成字符串
        $tmpStr = implode($tmpArr);
        // 对这个字符串进行MD5加密，即可获得Signature
        $this->Signature(md5($tmpStr));

        return $this->headers;
    }
}
