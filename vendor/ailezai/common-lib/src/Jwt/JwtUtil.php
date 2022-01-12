<?php
namespace AiLeZai\Common\Lib\Jwt;

use Exception;
use AiLeZai\Common\Lib\Log\LOG;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Claim;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\ValidationData;

/**
 * Class JwtUtil
 *
 * @package AiLeZai\Common\Lib\Jwt
 * @link https://github.com/lcobucci/jwt
 */
class JwtUtil
{
    /**
     * 生成简单的Token
     *
     * @param array   $params     数据参数（key => value)
     * @param string  $sign       加密串（文件或字符串）
     * @param string  $jwtType    签名方式
     *
     * @see JwtAlgEnum 可选的签名方式
     *
     * @return String
     *
     * @throws Exception
     */
    public static function generateSimpleToken(array $params, string $sign, string $jwtType = 'RS256')
    {
        // 获取加密方式信息
        $jwt = JwtAlgEnum::getName($jwtType);
        if (empty($jwt)) {
            throw new Exception('加密方式不存在');
        }

        // 生成签名类
        $class = JwtAlgEnum::getClass($jwt);
        $signer = new $class();

        // 设置签名
        $signature = new Key($sign);

        // 生成Jwt
        $token = (new Builder());
        foreach ($params as $key => $value) {
            $token = $token->set($key, $value);
        }
        $token = $token->sign($signer,  $signature)
            ->getToken();

        return (string) $token;
    }

    /**
     * 生成完整的Token
     *
     * @param string  $issuer     签发方
     * @param string  $audience   接收方
     * @param string  $id         唯一标识符
     * @param integer $notBefore  开始生效时间（秒）
     * @param integer $expiration 过期时间（秒）
     * @param array   $params     数据参数（key => value)
     * @param string  $sign       加密串（文件或字符串）
     * @param string  $jwtType    签名方式
     *
     * @see JwtAlgEnum 可选的签名方式
     *
     * @return String
     *
     * @throws Exception
     */
    public static function generateStdToken(string $issuer, string $audience, string $id, int $notBefore, int $expiration,
                                         array $params, string $sign, string $jwtType = 'RS256')
    {
        // 获取加密方式信息
        $jwt = JwtAlgEnum::getName($jwtType);
        if (empty($jwt)) {
            throw new Exception('加密方式不存在');
        }

        // 签名时间
        $issuedAt = time();

        // 生成签名类
        $class = JwtAlgEnum::getClass($jwt);
        $signer = new $class();

        // 设置签名
        $signature = new Key($sign);

        // 生成Jwt
        $token = (new Builder())->setIssuer($issuer)
            ->setAudience($audience)
            ->setId($id, true)
            ->setIssuedAt($issuedAt)
            ->setNotBefore($issuedAt + $notBefore)
            ->setExpiration($issuedAt + $expiration);
        foreach ($params as $key => $value) {
            $token = $token->set($key, $value);
        }
        $token = $token->sign($signer,  $signature)
            ->getToken();

        return (string) $token;
    }

    /**
     * 签名校验
     *
     * @param string  $token      字符串
     * @param string  $sign       解密串（文件或字符串）
     * @param string  $jwtType    签名方式
     *
     * @return bool
     *
     * @throws Exception
     */
    public static function verifyToken(string $token, string $sign, string $jwtType = 'RS256')
    {
        $jwt = JwtAlgEnum::getName($jwtType);
        if (empty($jwt)) {
            throw new Exception('加密方式不存在');
        }

        // 生成签名类
        $class = JwtAlgEnum::getClass($jwt);
        $signer = new $class();

        // 设置签名
        $signature = new Key($sign);

        $token = (new Parser())->parse($token);
        return $token->verify($signer, $signature);
    }

    /**
     * 合法性校验
     * 1. 校验是否在有效时间段内
     * 2. 校验签发方（若有）
     * 3. 校验接收方（若有）
     * 4. 校验唯一标识符（若有）
     *
     * @param string  $token      字符串
     * @param string  $issuer     签发方
     * @param string  $audience   接收方
     * @param string  $id         唯一标识符
     *
     * @return bool
     */
    public static function validate(string $token, string $issuer = '', string $audience = '', string $id = '')
    {
        $token = (new Parser())->parse($token);

        $data = new ValidationData();
        if (!empty($issuer)) {
            $data->setIssuer($issuer);
        }
        if (!empty($audience)) {
            $data->setAudience($audience);
        }
        if (!empty($id)) {
            $data->setId($id);
        }

        return $token->validate($data);
    }

    /**
     * 判断Token是否过期
     *
     * @param string  $token      字符串
     *
     * @return bool
     */
    public static function isExpire(string $token)
    {
        $token = (new Parser())->parse($token);

        return $token->isExpired();
    }

    /**
     * 返回Token对象
     *
     * @param string  $token      字符串
     *
     * @return \Lcobucci\JWT\Token
     */
    public static function getToken(string $token)
    {
        return (new Parser())->parse($token);
    }

    /**
     * 获取Token所有Header信息
     *
     * @param string  $token      字符串
     *
     * @return array
     */
    public static function getHeaders(string $token)
    {
        $token = (new Parser())->parse($token);

        return $token->getHeaders();
    }

    /**
     * 获取Token指定Header信息
     *
     * @param string  $token      字符串
     * @param string  $key        键
     * @param mixed   $default    默认值
     *
     * @return array
     */
    public static function getHeader(string $token, string $key, string $default = null)
    {
        $token = (new Parser())->parse($token);

        return $token->getHeader($key, $default);
    }

    /**
     * 获取Token所有payload信息
     *
     * @param string  $token      字符串
     *
     * @return array
     */
    public static function getClaims(string $token)
    {
        $token = (new Parser())->parse($token);

        return $token->getClaims();
    }

    /**
     * 获取Token指定payload信息
     *
     * @param string  $token      字符串
     * @param string  $key        键
     * @param mixed   $default    默认值
     *
     * @return array
     */
    public static function getClaim(string $token, string $key, string $default = null)
    {
        $token = (new Parser())->parse($token);

        return $token->getClaim($key, $default);
    }

    /**
     * 验证签名: 若成功返回将payload以数组形式返回, 失败返回false
     * // 该方法保证不抛出异常
     *
     * @param string $token
     *            `$Header.$Payload.$Signature`
     * @param string $secret
     *            签名密钥
     * @param string $algorithm
     *            签名算法 (默认:RS256)
     * @return boolean|array
     */
    public static function verifyAndGetPayloadArr($token, $secret, $algorithm = 'RS256')
    {
        if (empty(JwtAlgEnum::getName($algorithm))) {
            //throw new Exception('加密方式不支持');
            return false; // 签名算法不支持
        }

        try {
            // 生成签名类
            $alg_impl_class = JwtAlgEnum::getClass($algorithm);
            $signer = new $alg_impl_class();

            // 初始化签名key
            $signKey = new Key($secret);

            $jwtObj = (new Parser())->parse($token);

            if (!$jwtObj->verify($signer, $signKey)) {
                return false; // 签名失败
            } else {
                $payloadArr = array();
                foreach ($jwtObj->getClaims() as $key => $claim) {
                    /** @var $claim Claim */
                    $payloadArr[$key] = $claim->getValue();
                }
                return $payloadArr;
            }
        } catch (\Exception $e) {
            LOG::error(sprintf('JWT parse error: %s', LOG::e2str($e)));
            return false;
        }
    }

}
