<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2018/7/19
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use AiLeZai\Common\Lib\Log\LOG;

class DemoCommand extends Command
{
    /**
     * 执行命令
     *
     * @var string
     */
    protected $signature = 'demo:command';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '示例定时任务';

    public function handle()
    {
        /*
         * LOG作为日志打印在服务器，用于命令执行内容查看
         * echo 方法作为控制台输出，用于在cronsun上进行日志记录
         * 实际根据需要可以进行二选一，推荐在该类下使用echo
         * cronsun在0.3.2版本已经支持了Reporter角色，cronsun升级后可以使用
         */
        LOG::command("command({$this->signature})执行开始\n");
        // TODO
        LOG::command("command({$this->signature})执行结束\n");
    }
}