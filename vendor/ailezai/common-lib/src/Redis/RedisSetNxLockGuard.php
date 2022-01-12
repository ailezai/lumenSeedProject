<?php
namespace AiLeZai\Common\Lib\Redis;

class RedisSetNxLockGuard
{

    private $conf_key;

    private $lock_name;

    private $value;

    /**
     * 对象构造时, 自动加锁
     *
     * @param string $conf_key redis实例的配置key
     * @param string $lock_name 锁名称
     * @param string $value 锁内容,一般传入traceId
     * @param int $ttl 锁超时时间,默认40秒
     * @param int $max_try_times 加锁重试次数,默认3次
     * @throws \Exception
     */
    public function __construct($conf_key, $lock_name, $value = null, $ttl = 40, $max_try_times = 3)
    {
        $this->conf_key = $conf_key;
        $this->lock_name = $lock_name;
        $this->value = $value;

        for ($i = 0; $i < $max_try_times; $i ++) {
            if (RedLockImpl::lock($this->conf_key, $this->lock_name, $this->value, $ttl)) {
                return; // 加锁成功, 返回
            }

            // 从第2次开始, 随机sleep 0.1~1秒, 再尝试加锁
            usleep(mt_rand(100000, 1000000));
        }

        throw new \Exception("[{$this->conf_key},{$this->lock_name},{$this->value}] try-lock $i times failed");
    }

    /**
     * 对象析构时, 自动解锁
     * 可以使用 gc_collect_cycles(); 保证执行析构
     *
     * @throws \Exception
     * Note:
     * Attempting to throw an exception from a destructor (called in the time of script termination)
     * causes a fatal error.
     */
    public function __destruct()
    {
        $ret = RedLockImpl::unlock($this->conf_key, $this->lock_name, $this->value);
        if ($ret === 1) {
            return; // 解锁成功, 返回
        }

        throw new \Exception("[{$this->conf_key},{$this->lock_name},{$this->value}] unlock failed!!! cur_value[$ret]");
    }
}

