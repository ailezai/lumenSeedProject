<?php
namespace AiLeZai\Common\Lib\Redis;

use AiLeZai\Common\Lib\Config\IConfigCache;
use AiLeZai\Common\Lib\Log\LOG;
use AiLeZai\Common\Lib\Log\BackTrace;

class RedisHelper
{

    public static $flag__ping_check_next_time = array();

    /** @var RedisHelper[] */
    private static $_instanceMap;

    /**
     * @param $conf_key
     * @param $pingCheck
     * @return \Redis
     * @throws \Exception
     */
    private static function _get_conn($conf_key, $pingCheck)
    {
        // 若当前key对应的实例为空, 则new一个实例
        if (empty(self::$_instanceMap[$conf_key])) {
            self::$_instanceMap[$conf_key] = new RedisHelper($conf_key);
        }

        if (! isset(self::$flag__ping_check_next_time[$conf_key])) {
            self::$flag__ping_check_next_time[$conf_key] = false;
        }

        // 当`$flag__ping_check_next_time`被置为true, 或者入参$pingCheck=true时
        // 执行一次ping命令, 检测当前连接和ctx是否正常. 若异常则新建一个RedisHelper对象
        if ($pingCheck || self::$flag__ping_check_next_time[$conf_key]) {
            self::$flag__ping_check_next_time[$conf_key] = false;

            self::$_instanceMap[$conf_key]->checkAndReconnect(true);
        }

        return self::$_instanceMap[$conf_key];
    }

    /**
     * @param $conf_key
     * @param bool $pingCheck
     * @return \Redis
     * @throws \Exception
     */
    public static function getConn($conf_key, $pingCheck = false)
    {
        return self::_get_conn($conf_key, $pingCheck);
    }

    /**
     * @param $conf_key
     * @param bool $pingCheck
     * @return \Redis
     */
    public static function getConn_nothrow($conf_key, $pingCheck = false)
    {
        try {
            return self::_get_conn($conf_key, $pingCheck);
        } catch (\Exception $e) {
            return new \Redis(); // 返回一个空壳对象
        }
    }

    // ========== . ==========

    use IConfigCache;

    /** @var \Redis */
    private $redis;

    private $conf_key;

    /**
     * RedisHelper constructor.
     * @param $conf_key
     * @throws \Exception
     */
    public function __construct($conf_key)
    {
        $this->conf_key = $conf_key;

        // $conf_key为空时 构造一个假对象
        if (! empty($conf_key)) {
            $this->initConfigKey("/redis/" . $conf_key);
            $this->_connect();
        }
    }

    /**
     * @throws \Exception
     */
    protected function _connect()
    {
        try {
            $host = $this->current_conf['host'] ?? ''; // 配置错误时 host为空
            $port = $this->current_conf['port'] ?? 6379;
            $auth = $this->current_conf['auth'] ?? '';
            $db = $this->current_conf['index'] ?? 0;

            $this->redis = new \Redis();

            $this->redis->connect($host, $port, 3);
            if (! empty($auth)) {
                $this->redis->auth($auth);
            }
            if (! empty($db)) {
                $this->redis->select($db);
            }
        } catch (\Exception $e) {
            LOG::error(sprintf('conf%s _connect() from %s Exception: %s~%s %s:%d ', json_encode($this->current_conf), BackTrace::getCallerTrace(5, true), $e->getCode(), $e->getMessage(), basename($e->getFile()), $e->getLine()));
            throw $e;
        }
    }

    /**
     * @param $pingCheck
     * @throws \Exception
     */
    protected function checkAndReconnect($pingCheck)
    {
        if ($this->hasConfigChanged() || empty($this->redis) || ($pingCheck && ! $this->isConnectionValid())) {
            $this->_connect();
        }
    }

    protected function isConnectionValid()
    {
        // 同步调用echo命令, 参数是当前线程id.
        // 若 抛异常 或 得到的响应结果不等于当前线程id, 则重建连接
        // 这样保证 1.当前redis连接正常 2.req和reply是同步对应的
        // size_t thread_id = OS::getThreadId();
        // return (sendAndRecv(CmdBuilder{"ECHO", thread_id})->getAs<size_t>() == thread_id);

        // PHP是单线程版本, 直接用ping()检测就ok
        try {
            return $this->redis->ping() == '+PONG';
        } catch (\Exception $e) {
            LOG::error(sprintf('conf%s ping() from %s Exception: %s~%s %s:%d ', json_encode($this->current_conf), BackTrace::getCallerTrace(5, true), $e->getCode(), $e->getMessage(), basename($e->getFile()), $e->getLine()));
            return false;
        }
    }

    public function __call($name, $arguments)
    {
        $ret = null;

        try {
            $ret = call_user_func_array(array(
                $this->redis,
                $name
            ), $arguments);
        } catch (\Exception $e) {
            LOG::error(sprintf('conf%s %s%s from %s except: %s~%s %s:%d ', json_encode($this->current_conf), $name, json_encode($arguments), BackTrace::getCallerTrace(2, true), $e->getCode(), $e->getMessage(), basename($e->getFile()), $e->getLine()));
            self::$flag__ping_check_next_time[$this->conf_key] = true; // 安排下一次操作时 用ping()做连接检查
        }

        return $ret;
    }
}

