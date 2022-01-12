<?php
namespace System\Supports;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Exception;

class TwoFactorAuthenticationUtil
{
    /**
     * @var int 验证码长度
     */
    protected static $codeLength = 6;

    /**
     * 创建密钥
     *
     * @param int $secretLength 密钥长度
     *
     * @return string
     *
     * @throws Exception
     */
    public static function createSecret($secretLength = 16)
    {
        $validChars = static::getBase32LookupTable();
        // Valid secret lengths are 80 to 640 bits
        if ($secretLength < 16 || $secretLength > 128) {
            throw new Exception('密钥长度限制为 16 到 128 位');
        }

        $secret = '';
        $rnd = false;
        if (function_exists('random_bytes')) {
            $rnd = random_bytes($secretLength);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes($secretLength, $cryptoStrong);
            if (!$cryptoStrong) {
                $rnd = false;
            }
        }

        if ($rnd !== false) {
            for ($i = 0; $i < $secretLength; ++$i) {
                $secret .= $validChars[ord($rnd[$i]) & 31];
            }
        } else {
            throw new Exception('没有安全的随机参数来源');
        }
        return $secret;
    }

    /**
     * 根据密钥计算验证码
     *
     * @param string $secret       密钥
     * @param int|null $timeSlice  时间片（有效时间段，单位秒）
     *
     * @return string
     */
    public static function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }
        $secretKey = static::base32Decode($secret);
        // Pack time into binary string
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretKey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashPart = substr($hm, $offset, 4);
        // Unpak binary value
        $value = unpack('N', $hashPart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, static::$codeLength);
        return str_pad($value % $modulo, static::$codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * 获取二维码
     *
     * @param string $name      账号名称
     * @param string $secret    密钥
     * @param array  $faParams  验证码参数
     * @param array  $qrParams  二维码参数
     * @param string $file      保存路径（绝对路径）
     *
     * @return string
     */
    public static function getQRCode($name, $secret, $faParams = [], $qrParams = [], $file = "")
    {
        $urlencoded = "otpauth://totp/";
        if (!empty($faParams['issuer'])) {
            $urlencoded .= "{$faParams['issuer']}:";
        }
        $urlencoded .= $name;
        $urlencoded .= "?secret={$secret}";
        if (!empty($faParams['issuer'])) {
            $urlencoded .= "&issuer={$faParams['issuer']}";
        }
        $urlencoded .= "&algorithm=" . ($faParams['algorithm'] ?? "SHA1");
        $urlencoded .= "&digits=" . ($faParams['digits'] ?? "6");
        $urlencoded .= "&period=" . ($faParams['period'] ?? "30");

        $qrCode = new QrCode($urlencoded);
        $qrCode->setSize($qrParams['size'] ?? 200);
        $qrCode->setWriterByName($qrParams['type'] ?? 'png');
        $qrCode->setMargin($qrParams['margin'] ?? 10);
        $qrCode->setEncoding($qrParams['encoding'] ?? 'UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::MEDIUM);
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $qrCode->setRoundBlockSize(true);
        $qrCode->setValidateResult(false);

        if (!empty($file)) {
            $qrCode->writeFile($file);
        }

        return $qrCode->writeString();
    }

    /**
     * 校验验证码
     *
     * @param string $secret    密钥
     * @param string $code      验证码
     * @param int $discrepancy  ±误差时间片数
     * @param int|null $currentTimeSlice 当前时间片
     *
     * @return bool
     */
    public static function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }
        if (strlen($code) != 6) {
            return false;
        }
        for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
            $calculatedCode = static::getCode($secret, $currentTimeSlice + $i);
            if (static::timingSafeEquals($calculatedCode, $code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 设置验证码长度
     *
     * @param int $length
     */
    public static function setCodeLength($length)
    {
        static::$codeLength = $length;
    }

    /**
     * 安全值比较
     * more info here: http://blog.ircmaxell.com/2014/11/its-all-about-time.html.
     *
     * @param string $safeString 安全校验值
     * @param string $userString 用户验证码
     *
     * @return bool True if the two strings are identical
     */
    private static function timingSafeEquals($safeString, $userString)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($safeString, $userString);
        }
        $safeLen = strlen($safeString);
        $userLen = strlen($userString);
        if ($userLen != $safeLen) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < $userLen; ++$i) {
            $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
        }
        // They are only identical strings if $result is exactly 0...
        return $result === 0;
    }

    /**
     * 获取32个字符数组用于Base32的编码/解码
     *
     * @return array
     */
    protected static function getBase32LookupTable()
    {
        return [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '=',  // padding char
        ];
    }

    /**
     * Base32解码
     *
     * @param string|null $secret 密钥
     *
     * @return bool|string
     */
    protected static function base32Decode($secret)
    {
        if (empty($secret)) {
            return '';
        }

        $base32chars = static::getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);
        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues = [6, 4, 3, 1, 0];
        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }

        for ($i = 0; $i < 4; ++$i) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) {
                return false;
            }
        }

        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) {
                return false;
            }
            for ($j = 0; $j < 8; ++$j) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); ++$z) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }
        return $binaryString;
    }
}