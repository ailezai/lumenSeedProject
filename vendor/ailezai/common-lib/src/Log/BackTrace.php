<?php
namespace AiLeZai\Common\Lib\Log;

class BackTrace
{

    /**
     * f();
     *
     * function f() {
     * $ctx = BackTrace::getCallerCtx();
     * $ctx['file'] = `f(); 这个点的file`
     * $ctx['line'] = `f(); 这个点的lile`
     * $ctx['function'] = `f(); 这个点所属的function`
     * }
     *
     * #0 BackTrace::getCallerTrace() called at [111.php:11]
     * #1 封装的lib方法() called at [222.php:22]
     * #2 业务调用方法() called at [333.php:33]
     *
     * 返回结果: 取#2的 `[222.php:22]` 和#3的 `业务调用方法()`
     * 含义: 在哪个业务方法的哪个位置调用了封装lib函数
     * @param int $biz_func_idx PHP的backtrace中, 业务函数一般是从idx=2开始
     * @param bool $to_str 是否返回string形式
     * @return array|mixed|null|string
     */
    public static function getCallerTrace($biz_func_idx = 2, $to_str = false)
    {
        if ($biz_func_idx < 1) {
            return null;
        }

        $limit = $biz_func_idx + 1;

        // PHP7不会包含`call_user_func` and `call_user_func_array`, 所以层次会少一层
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit);
        } else if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit * 2);
        } else {
            $bt = debug_backtrace();
        }
        // debug_print_backtrace();

        $frameno = 0;
        $ctx = array();

        $N = count($bt);
        for ($i = 0; $i < $N; $i ++) {
            // 如果是虚拟节点, 跳过
            if (empty($bt[$i]['file']) || empty($bt[$i]['line'])) {
                continue;
            }

            // 对于php7以前的版本, 需要特殊处理下 call_user_func, call_user_func, __call, __callStatic
            $tmp = $bt[$i];
            if ($tmp['function'] == 'call_user_func' || $tmp['function'] == 'call_user_func_array') {
                // 合并前一个stack的func,type等信息
                $prev = $bt[$i - 1];
                if (empty($prev['file']) || empty($prev['line'])) {
                    $tmp = array_merge($tmp, $prev);
                }
            } else if ($tmp['function'] == '__call' || $tmp['function'] == '__callStatic') {
                if (! empty($bt[$i + 1])) {
                    // 如果下一个stack file,line都与当前stack相等, 跳过它 (因为它是不存在的方法)
                    $next = $bt[$i + 1];
                    if (! empty($next['file']) && $tmp['file'] == $next['file'] && ! empty($next['line']) && $tmp['line'] == $next['line']) {
                        $i ++;
                    }
                }
            }
            $ctx[$frameno] = $tmp;

            $frameno ++;
            if ($frameno == $limit) {
                break; // 找到对应trace点
            }
        }
        // var_dump($ctx);

        if (empty($ctx[$biz_func_idx])) {
            if (empty($ctx[$biz_func_idx - 1])) {
                // bt层次不够,返回 unknown
                return ($to_str) ? '#unknown' : array(
                    'function' => 'unknown'
                );
            } else {
                $trace = array(
                    'function' => '{main}'
                );
            }
        } else {
            $trace = $ctx[$biz_func_idx];
        }

        // file:line 取上一层的
        $trace['file'] = basename($ctx[$biz_func_idx - 1]['file']);
        $trace['line'] = $ctx[$biz_func_idx - 1]['line'];

        if ($to_str) {
            $str = sprintf('%s:%d', $trace['file'], $trace['line']);
            if (! empty($trace['function'])) {
                $str .= '#';
                // if (! empty($trace['class'])) {
                // $str .= $trace['class'] . $trace['type'];
                // }
                $str .= $trace['function'];
            }
            return $str;
        } else {
            return $trace;
        }
    }
}


/*
// 在普通func内部
function aaa()
{
    $trace = BackTrace::getCallerTrace();
    var_dump($trace);
    exit();
    var_dump('aaa');
}

// 在闭包内部
function bbb()
{
    call_user_func(function () {
        // $trace = BackTrace::getCallerTrace(); var_dump($trace); exit();

        aaa();
    });
}

// 在__callStatic内部
class CCC
{

    public static function __callStatic($name, $arguments)
    {
        // $trace = BackTrace::getCallerTrace(); var_dump($trace); exit();
        bbb();
    }
}

// 在__call内部
class DDD
{

    public function __call($name, $arguments)
    {
        // $trace = BackTrace::getCallerTrace(); var_dump($trace); exit();
        CCC::ccc_func();
    }
}

// 在__invoke内部
class EEE
{

    public function __invoke()
    {
        // $trace = BackTrace::getCallerTrace(); var_dump($trace); // exit();
        $d = new DDD();
        $d->ddd_func();
    }
}

// ==================

// 组合各种层次

$e = new EEE();

call_user_func(function () use ($e) {
    call_user_func($e);
});

*/

//  /usr/bin/php BackTrace.php > php53.txt && php BackTrace.php > php71.txt
