<?php
namespace AiLeZai\Common\Lib\Redis;

use AiLeZai\Common\Lib\Log\LOG;

class RedLockImpl
{

    /**
     * @param string $conf_key redis实例的配置key
     * @param string $lock_key_name 锁的名称
     * @param string $value 锁内部的值(解锁时需要判断一致) 一般用traceId
     * @param int $ttl 锁超时时间(秒)
     * @return bool
     * @throws \Exception
     */
    public static function lock($conf_key, $lock_key_name, $value = null, $ttl = 40)
    {
        if (empty($value)) {
            $value = LOG::getTraceId();
        }

        return RedisHelper::getConn($conf_key)->set($lock_key_name, $value, array(
            'nx',
            'ex' => $ttl
        ));
    }

    /**
     * @param string $conf_key redis实例的配置key
     * @param string $lock_key_name 锁的名称
     * @param string $value 锁内部的值(和加锁时的值一致)
     * @return int|string 1~解锁成功, "$value"~解锁失败,返回当前锁内部的值
     * @throws \Exception
     */
    public static function unlock($conf_key, $lock_key_name, $value = null)
    {
        if (empty($value)) {
            $value = LOG::getTraceId();
        }

        // 解锁成功返回`1`, 解锁失败返回`$cur_value`
        $script = '
local cur_value = redis.call("get",KEYS[1])
if cur_value == ARGV[1] then
    return redis.call("del",KEYS[1])
else
    return cur_value
end
';

        // 参考: https://github.com/ronnylt/redlock-php/blob/7f75de41b0c6fb7f0653d6588ab800b2f3f9c862/src/RedLock.php#L111
        return RedisHelper::getConn($conf_key)->eval($script, array(
            $lock_key_name,
            $value
        ), 1);
    }
}

