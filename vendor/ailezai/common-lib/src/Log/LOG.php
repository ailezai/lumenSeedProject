<?php

namespace AiLeZai\Common\Lib\Log;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

use AiLeZai\Common\Lib\Config\CFG;

/**
 * Created by PhpStorm.
 * User: sangechen
 * Date: Apr-25
 * Time: 17:15
 *
 * @method static bool info(string $msg, array $context = array(), string $level_name = '', int $bt_idx_start_offset = 0)   INFO 日志: 和nginx的access_log类似, 不论成功/失败 都打印一条
 * @method static bool error(string $msg, array $context = array(), string $level_name = '', int $bt_idx_start_offset = 0)  ERROR日志: 失败时 多打印一条
 * @method static bool debug(string $msg, array $context = array(), string $level_name = '', int $bt_idx_start_offset = 0)  DEBUG日志: 运行中间结果/明细打印 (应该尽量少)
 */
class LOG
{

    /**
     *
     * @var Logger[]
     */
    private static $loggers;

    /**
     * 得到一个logger, 底层文件名是 "${file_base_name}${HTTP_HOST}__$name.log"
     * @param string $name
     * @return Logger
     */
    private static function initAndGetLogger($name)
    {
        if (empty(self::$loggers[$name])) {
            $category = CFG::get("/log/$name/category", CFG::get('/log/category', "_default_"));
            $file_base_name = CFG::get("/log/$name/file_base_name", CFG::get('/log/file_base_name', "/data/logs/_default_/"));
            $host_prefix = CFG::get("/log/$name/host_prefix", CFG::get('/log/host_prefix', true));
            $max_files = CFG::get("/log/$name/max_files", CFG::get('/log/max_files', 31)); //保留1个月
            $level = CFG::get("/log/$name/level", CFG::get('/log/level', Logger::DEBUG));

            if ($host_prefix && !empty($_SERVER['HTTP_HOST'])) {
                $file_base_name .= ($_SERVER['HTTP_HOST'] . '__');
            }

            $logger = new Logger($category);
            $handler = new RotatingFileHandler("{$file_base_name}{$name}.log", $max_files, $level, true, 0666); // 0666保证log文件可以被其他 用户/进程 读写
            // 2017-04-25 19:01:01.123456 web.jjb:INFO T<abcd1234~1234> {msg}`|{ctx}` - test.php:55#log{必须打印业务函数} - getmypid()
            $formatter = new LineFormatter("%datetime% %channel%:%level_name% %message% %context% %extra%\n", "Y-m-d H:i:s.u", false, true);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            self::$loggers[$name] = $logger;

            self::initTraceId();
        }

        return self::$loggers[$name];
    }

    private static $pid;
    private static $traceId;

    public static function initTraceId()
    {
        self::$pid = getmypid();
        if (!isset(self::$traceId)) {
            if (!isset($GLOBALS['traceId'])) {
                self::$traceId = self::$pid . '~' . uniqid();
            } else {
                self::$traceId = $GLOBALS['traceId'];
            }
        }
    }

    public static function getTraceId()
    {
        if (!isset(self::$traceId)) {
            self::initTraceId();
        }
        return self::$traceId;
    }

    public static function setTraceId($traceId)
    {
        self::$pid = getmypid();
        self::$traceId = $traceId;
    }

    public static function __callStatic($name, $arguments)
    {
        $logger = self::initAndGetLogger($name);

        $msg = empty($arguments[0]) ? '' : $arguments[0];
        $context = empty($arguments[1]) ? array() : $arguments[1];
        $level_name = empty($arguments[2]) ? $name : $arguments[2];
        $bt_idx_start_offset = empty($arguments[3]) ? 0 : intval($arguments[3]);

        $level = Logger::toMonologLevel($level_name);
        if (!is_int($level)) {
            $level = Logger::INFO;
        }

        // bt数组第$idx元素是当前行, 第$idx+1元素表示上一层
        // 另外 function,class 要再往上取一个层次

        // PHP7不会包含`call_user_func` and `call_user_func_array`, 所以层次会少一层
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $idx = 0 + $bt_idx_start_offset;
        } else {
            $bt = debug_backtrace();
            $idx = 1 + $bt_idx_start_offset;
        }

        $trace = basename($bt[$idx]['file']) . ':' . $bt[$idx]['line'];
        if (!empty($bt[$idx + 1]['function'])) {
            $trace .= '#';
            // if (! empty($bt[$idx+1]['class'])) {
            // $trace .= $bt[$idx+1]['class'] . $bt[$idx+1]['type'];
            // }
            $trace .= $bt[$idx + 1]['function'];
        }

        $message = sprintf('T<%s> %s - %s - %d', self::$traceId, $msg, $trace, self::$pid);

        return $logger->addRecord($level, $message, $context);
    }

    /**
     * @param $e \Exception
     * @return string
     */
    public static function e2str($e)
    {
        return sprintf("<%s~%s@%s:%d>", $e->getCode(), $e->getMessage(), basename($e->getFile()), $e->getLine());
    }

    /**
     * @param $var mixed
     * @param int $start 参考substr()
     * @param int $length 参考substr()
     * @param string $fmt 需要包含`%s`占位符; 如果有切割,用sprintf($fmt, $)修饰后返回
     * @return string
     */
    public static function varToString($var, $start = null, $length = null, $fmt = null)
    {
        if ($var instanceof \Exception) {
            $str = LOG::e2str($var);
        } elseif (is_array($var) || is_object($var)) {
            $str = json_encode($var, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $str = strval($var);
        }

        if (is_null($start)) {
            return $str;
        } else {
            if (is_null($length)) {
                $str2 = substr($str, $start);
            } else {
                $str2 = substr($str, $start, $length);
            }

            if (!is_null($fmt) && $str2 !== $str) {
                return sprintf($fmt, $str2);
            } else {
                return $str2;
            }
        }
    }

}
