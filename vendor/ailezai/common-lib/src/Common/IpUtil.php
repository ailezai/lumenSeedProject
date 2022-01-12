<?php

namespace AiLeZai\Common\Lib\Common;

class IpUtil
{
    /**
     * 获取真实IP
     *
     * @param bool $validate
     *
     * @return string
     */
    public static function getCurrentIP($validate = true)
    {
        $ipkeys = array(
            'REMOTE_ADDR',
            'HTTP_X_FORWARDED_FOR', // 阿里云使用 X-Forwarded-For
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'HTTP_X_CLUSTER_CLIENT_IP'
        );

        $ip = '';
        $last_ip = 'unknown';
        foreach ($ipkeys as $key) {
            if (isset($_SERVER[$key])) {
                foreach (preg_split("/[\s,;]+/", $_SERVER[$key], null, PREG_SPLIT_NO_EMPTY) as $last_ip) {
                    if (! $validate || ! self::isReservedIP($last_ip)) {
                        $ip = $last_ip;
                        return $ip; // only return one ip
                    }
                }
            }
        }

        return empty($ip) ? $last_ip : $ip; // implode(", ", $ip));
    }

    /**
     * 判断IP地址是否为预留地址
     * 参考: https://en.wikipedia.org/wiki/Reserved_IP_addresses
     *
     * 阿里云SLB: 10.158.0.0/16，10.159.0.0/16，10.49.0.0/16，100.64.0.0/10
     *
     * @param string $ip IP地址
     *
     * @return boolean
     */
    private static function isReservedIP($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) == false) {
            return true;
        }

        $ip_dec = ip2long($ip);

        // 100.64.0.0/10
        // 100.64.0.0 - 100.127.255.255
        // 1681915904 - 1686110207
        if (1681915904 <= $ip_dec && $ip_dec <= 1686110207) {
            return true;
        }

        return false;
    }
}