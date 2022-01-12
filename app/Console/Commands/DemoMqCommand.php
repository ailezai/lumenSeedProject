<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2018/7/19
 */

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use AiLeZai\Common\Lib\Log\LOG;
use AiLeZai\Common\Lib\MsgQ\MsgQHelper;

class DemoMqCommand extends Command
{
    /**
     * 执行命令
     *
     * @var string
     */
    protected $signature = 'demo:mq_command';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '示例MQ';

    public function handle()
    {
        MsgQHelper::subscribeStr(
            '\App\Console\Commands\DemoMqCommand::executing',
            'demo_exchange_name',
            'demo_subscribe_routing_key',
            'demo_subscribe_queue_name'
        );
    }

    /**
     * MQ执行脚本
     *
     * @param $jsonData
     *
     * @throws Exception
     */
    public static function executing($jsonData)
    {
        // 示例用于标识MQ，请保持与类的'命令描述'一致
        $description = "示例";
        LOG::mq("MQ({$description})接受参数：{$jsonData}");
        $data = json_decode($jsonData, true);
        try {
            /*
             * 业务逻辑处理
             * 遇到特殊业务逻辑，无需重试时，请打印日志捕获异常
             * 遇到其他异常时，请打印日志并抛出，MQ队列会在指定时间（默认30s）后进行重试
             */
            // TODO
//        } catch (OtherException $e) {
//            LOG::mq("MQ({$description})处理异常：" . LOG::e2str($e));
        } catch (Exception $e) {
            LOG::mq("MQ({$description})处理异常：" . LOG::e2str($e));
            throw $e;
        }
        return;
    }
}