<?php

namespace AiLeZai\Common\Lib\Jwt;

use Exception;

class UserInfoHelper
{
    /**
     * @var string 加密方式
     */
    const CIPHER = 'DES';

    /**
     * @var string 秘钥
     */
    const KEY = 'jlldgdok';

    /**
     * @var string IV
     */
    const IV = 'jlldgdok';

    /**
     * 加密userInfo
     *
     * @param int    $userId     用户id
     * @param int    $loginTime  登录时间
     * @param string $checkCode  校验码
     *
     * @return string
     */
    public static function encrypt($userId, $loginTime, $checkCode)
    {
        $json = json_encode([$userId, $loginTime, $checkCode]);

        return bin2hex(openssl_encrypt($json, self::CIPHER, self::KEY, OPENSSL_RAW_DATA, self::IV));
    }

    /**
     * 解密userInfo
     *
     * @param string $userInfo
     *
     * @return array
     */
    public static function decrypt($userInfo)
    {
        try {
            $decrypt = openssl_decrypt(hex2bin($userInfo), self::CIPHER, self::KEY, OPENSSL_RAW_DATA, self::IV);
            $decryptArr = json_decode($decrypt, true);

            return [
                $decryptArr[0], // UserId
                $decryptArr[1], // LoginTime
                $decryptArr[2]  // CheckCode
            ];
        } catch (Exception $e) {
            return [];
        }
    }
}