<?php
namespace System\Supports;

use Exception;
use AiLeZai\Common\Lib\Jwt\JwtUtil;

class JwtHelper
{
    /**
     * 生成Jwt
     *
     * @param string $audience 接收方
     * @param string $id 唯一标识符
     * @param integer $notBefore 开始生效时间（秒）
     * @param integer $expiration 过期时间（秒）
     * @param array $params 数据参数（key => value)
     * @param string $key 生成密钥
     *
     * @return String
     * @throws Exception
     */
    public static function generateToken(string $audience = null, string $id = null, int $notBefore = null, int $expiration = null,
                                array $params = [], $key = 'PRIVATE')
    {
        if ($key == 'PUBLIC') {
            $key = env('RSA_256_PUBLIC_KEY', '');
        } else {
            $key = env('RSA_256_PRIVATE_KEY', '');
        }
        return JwtUtil::generateStdToken(env('APP_NAME'), $audience, $id, $notBefore, $expiration,
            $params, $key, 'RS256');
    }

    /**
     * 签名校验
     *
     * @param string $token
     * @param string $key 校验密钥
     *
     * @return bool
     *
     * @throws Exception
     */
    public static function verifyToken(string $token, $key = 'PUBLIC')
    {
        if ($key == 'PRIVATE') {
            $key = env('RSA_256_PRIVATE_KEY', '');
        } else {
            $key = env('RSA_256_PUBLIC_KEY', '');
        }
        return JwtUtil::verifyToken($token, $key, 'RS256');
    }
}