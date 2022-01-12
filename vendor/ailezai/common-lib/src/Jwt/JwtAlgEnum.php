<?php
namespace AiLeZai\Common\Lib\Jwt;

use ReflectionClass;

/**
 * Jwt 签名方式枚举
 *
 * Class JwtAlgEnum
 * @package AiLeZai\Common\Lib\Jwt
 */
class JwtAlgEnum
{
    /**
     * 加密方式，加密算法，加密大小类，
     */
    const HS256 = ['HS256', 'Hmac', 'Lcobucci\JWT\Signer\Hmac\Sha256'];
    const HS384 = ['HS384', 'Hmac', 'Lcobucci\JWT\Signer\Hmac\Sha384'];
    const HS512 = ['HS512', 'Hmac', 'Lcobucci\JWT\Signer\Hmac\Sha512'];

    const RS256 = ['RS256', 'Rsa', 'Lcobucci\JWT\Signer\Rsa\Sha256'];
    const RS384 = ['RS384', 'Rsa', 'Lcobucci\JWT\Signer\Rsa\Sha384'];
    const RS512 = ['RS512', 'Rsa', 'Lcobucci\JWT\Signer\Rsa\Sha512'];

    const ES256 = ['ES256', 'Ecdsa', 'Lcobucci\JWT\Signer\Ecdsa\Sha256'];
    const ES384 = ['ES384', 'Ecdsa', 'Lcobucci\JWT\Signer\Ecdsa\Sha384'];
    const ES512 = ['ES512', 'Ecdsa', 'Lcobucci\JWT\Signer\Ecdsa\Sha512'];

    /**
     * 获取枚举类描述名称（默认为数组第0位）
     *
     * @param $param
     *
     * @return string
     */
    public static function getName($param)
    {
        $allConst = static::getAllConst();
        foreach ($allConst as $const) {
            if (!empty($const) && !empty($const[0]) && $const[0] == $param) {
                return $const[0];
            }
        }

        return '';
    }

    /**
     * @param $param
     *
     * @return string|array
     */
    public static function getType($param)
    {
        $allConst = static::getAllConst();
        foreach ($allConst as $const) {
            if (!empty($const) && !empty($const[0]) && $const[0] == $param) {
                return $const[1];
            }
        }

        return '';
    }

    /**
     * @param $param
     *
     * @return string|array
     */
    public static function getClass($param)
    {
        $allConst = static::getAllConst();
        foreach ($allConst as $const) {
            if (!empty($const) && !empty($const[0]) && $const[0] == $param) {
                return $const[2];
            }
        }

        return '';
    }

    /**
     * 获得该枚举类所有成员
     *
     * @return array
     */
    public static function getAllConst()
    {
        try {
            $reflect = new ReflectionClass(static::class);
            return $reflect->getConstants();
        } catch (\ReflectionException $e) {
            return [];
        }
    }
}
