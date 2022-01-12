<?php
/**
 * Created by PhpStorm.
 * User: sangechen
 * Date: Apr-22
 * Time: 15:14
 */

namespace AiLeZai\Common\Lib\Log;

use AiLeZai\Common\Lib\Jwt\Api\JwtHelper;

class ReqRespLogBuilder
{
    private $logger;

    // ===== resp params =====
    private $beforeTimeParts;
    private $afterTimeParts;

    private $startTime;

    // ===== resp params =====
    /**
     * @var float 调用服务耗时
     */
    public $durationSec;
    // private $output = null;
    // private $error = null;

    /**
     * 对象构造时, 初始化req相关信息
     * @param $logger string 日志文件名
     * @param $service string 调用的服务名
     * @param $interface string 调用的接口名
     * @param $input mixed 请求参数
     */
    public function __construct($logger, $service, $interface, $input)
    {
        $this->logger = $logger;

        // 日志格式: <服务> <接口> <耗时> <请求> <响应>
        //          `$beforeTimeParts`      `$afterTimeParts`
        $this->beforeTimeParts = sprintf("%s if[%s]", $service, $interface);

        // 目前来说 header只有jwt, traceId本身打日志就会带
        $this->afterTimeParts = sprintf("H=%s I=%s", JwtHelper::toLogStr(1), LOG::varToString($input));

        $this->durationSec = 0;
        $this->startTime = microtime(true);
    }

    /**
     * @param $output mixed 接口正常响应
     */
    public function respOk($output)
    {
        if (!empty($this->startTime)) {
            $this->durationSec = microtime(true) - $this->startTime;

            // 日志格式: `{<服务> <接口>}` <耗时> `{<请求>}` <正常响应>
            LOG::{$this->logger}(sprintf("%s %.3F %s  O=%s",
                $this->beforeTimeParts, $this->durationSec, $this->afterTimeParts,
                JwtHelper::filterPassThroughResp(LOG::varToString($output))),
                null, null, 1);

            $this->startTime = null;
        }
    }

    /**
     * @param $error mixed 接口错误信息
     */
    public function respError($error)
    {
        if (!empty($this->startTime)) {
            $this->durationSec = microtime(true) - $this->startTime;

            // 日志格式: `{<服务> <接口>}` <耗时> `{<请求>}` <错误信息>
            LOG::{$this->logger}(sprintf("%s %.3F %s  E=%s",
                $this->beforeTimeParts, $this->durationSec, $this->afterTimeParts,
                JwtHelper::filterPassThroughResp(LOG::varToString($error))),
                null, null, 1);

            $this->startTime = null;
        }
    }

    /**
     * 对象析构时, 自动打日志
     * 可以使用 gc_collect_cycles(); 保证执行析构
     */
    public function __destruct()
    {
        if (!empty($this->startTime)) {
            $this->durationSec = microtime(true) - $this->startTime;

            // 日志格式: `{<服务> <接口>}` <耗时> `{<请求>}` <错误信息>
            LOG::{$this->logger}(sprintf("%s %.3F %s  ==> __destruct() called!",
                $this->beforeTimeParts, $this->durationSec, $this->afterTimeParts),
                null, null, 1);

            $this->startTime = null;
        }
    }
}

