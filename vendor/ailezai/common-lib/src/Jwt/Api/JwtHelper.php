<?php

namespace AiLeZai\Common\Lib\Jwt\Api;

use AiLeZai\Common\Lib\Config\CFG;
use AiLeZai\Common\Lib\Jwt\JwtUtil;

class JwtHelper
{
    private static $initToken = null;
    private static $newToken = null;

    /**
     * 每个web请求都需要调用 JwtHelper::initByAuthHeader() 方法
     * jwt传输规范: Authorization: Bearer <token>
     * @return bool 签名是否验证通过
     */
    public static function initByAuthHeader()
    {
        // 优先取 HTTP_AUTHORIZATION, 没有再尝试取 REDIRECT_HTTP_AUTHORIZATION
        $Authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null);

        // Authorization为空 或 前缀不符合`Bearer`, 都认为是没传入 jwt
        if (empty($Authorization)
            || strncasecmp($Authorization, 'Bearer', 6) != 0) {
            $token = null;
        } else {
            // 将<token>提取出来 (可能为'' 空字符串)
            $token = trim(substr($Authorization, 6));
        }

        // 使用 null|string 做初始化
        return self::initToken($token);
    }

    /**
     * 初始化<token> 并 解析出 UserAuthData
     * @param $token string `$Header.$Payload.$Signature`
     * @return bool 签名是否验证通过
     */
    public static function initToken($token = null)
    {
        if (is_null($token)) {
            self::$initToken = null;
            self::$newToken = null;
            UserAuthData::initByJwtPayload(array());
        } else {
            $token = trim($token);

            if (empty($token)) {
                self::$initToken = '';
                self::$newToken = '';
                UserAuthData::initByJwtPayload(array()); // 这里置空, 但需要通过其他方式初始化 UserAuthData
            } else {
                // initToken不做校验, 直接设置
                self::$initToken = $token;
                self::$newToken = '';   // 如果token验签通过，会覆盖$newToken
                return self::updateToken(self::$initToken);
            }
        }

        return false;
    }

    /**
     * @return bool 请求中 是否 `不`包含jwt头信息
     */
    public static function isInitTokenNull()
    {
        return is_null(self::$initToken);
    }

    /**
     * {升级App后首次调接口会传<空>; 触发补偿token逻辑}
     * 命名说明: (为啥选blank?)
     * // isBlank(" ") => true
     * // isEmpty(" ") => false
     * @return bool 已传入的jwt头 是否为 <空>
     * @throws \Exception
     */
    public static function isInitTokenBlank()
    {
        if (self::isInitTokenNull()) {
            throw new \Exception('check isInitTokenNull() before isInitTokenBlank() !!!');
        }

        // 因为 $initToken 在初始化的时候做过了 trim(), 所以这里用php的 empty() 判断
        return empty(self::$initToken);
    }

    /**
     * 输出initToken的可打印精简字符串
     * null     => '<>'
     * <空>     => '<Bearer>'
     * <token>  => '<后8位>'
     * @return string
     */
    public static function initToLogStr()
    {
        if (is_null(self::$initToken)) {
            $str = '';
        } elseif (empty(self::$initToken)) {
            $str = 'Bearer';
        } else {
            $str = substr(self::$initToken, -8);
        }

        return sprintf('<%s>', $str);
    }


    /**
     * 补偿或刷新<token> 并 解析出 UserAuthData
     * @param $token string `$Header.$Payload.$Signature`
     * @return bool 签名是否验证通过
     */
    public static function updateToken($token)
    {
        if (empty($token)) {
            return false; // token为空, 直接返回; 数据不修改
        } elseif ($token === self::$newToken) {
            // token不变, UserAuthData也不用变
            // 签名校验也算通过
            return true;
        } else {
            // 解析, 验证格式, 验证签名
            $payloadArr = JwtUtil::verifyAndGetPayloadArr($token, CFG::get('/jwt/public_key'), 'RS256');
            if ($payloadArr === false) {
                return false; // 验签不通过, 直接返回; 数据不修改
            }

            self::$newToken = $token;
            UserAuthData::initByJwtPayload($payloadArr); // 将解析出的$Payload更新到 UserAuthData 中
            return true;
        }
    }

    /**
     * MQ消息传递时使用 (需要特殊处理null)
     * @return null|string 当前最新<token> (传递给下游服务)
     */
    public static function getToken()
    {
        return self::$newToken;
    }

    /**
     * 调用其他服务接口时,透传使用 (需要特殊处理null)
     * @param array $headers
     * @return bool
     */
    public static function addPassThroughAuthHeader(&$headers)
    {
        if (is_null(self::$newToken)) {
            return false;
        } else {
            $headers['Authorization'] = sprintf('Bearer %s', self::$newToken);
            return true;
        }
    }

    /**
     * 将相应内容中的token替换成 <NOT-MODIFIED>, 精简日志
     * @param string $raw_output
     * @return string
     */
    public static function filterPassThroughResp($raw_output)
    {
        if (!empty(self::$newToken)) {
            return str_replace(self::$newToken, '<NOT-MODIFIED>', $raw_output);
        } else {
            return $raw_output;
        }
    }

    /**
     * @return null|string 响应给客户端的<token> (返回null表示不更新客户端的<token>)
     */
    public static function getRespToken()
    {
        // 如果新token和初始token一样, 返回<空>
        return (self::$newToken == self::$initToken) ? null : self::$newToken;
    }

    /**
     * 输出newToken的可打印精简字符串
     * null     => '<>'
     * <空>     => ''         [type=1时 输出'<Bearer>']
     * <token>  => '<后8位>'  [type=2时 可能输出'<>']
     * @param int $type 0~默认 1~请求 2~响应
     * @return string
     */
    public static function toLogStr($type = 0)
    {
        if (is_null(self::$newToken)) {
            $str = '';
        } elseif (empty(self::$newToken)) {
            $str = ($type == 1) ? 'Bearer' : '';
        } else {
            $token = ($type == 2) ? self::getRespToken() : self::$newToken;
            $str = substr($token, -8);
        }

        return sprintf('<%s>', $str);
    }
}
