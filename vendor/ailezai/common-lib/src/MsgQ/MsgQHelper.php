<?php
namespace AiLeZai\Common\Lib\MsgQ;

use AiLeZai\Common\Lib\Common\ScopeExit;
use AiLeZai\Common\Lib\Prometheus\Application\TaskHistogram;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use AiLeZai\Common\Lib\Config\CFG;
use AiLeZai\Common\Lib\Log\LOG;

/**
 * Created by eclipse-php.
 * User: chenzhuren
 * Date: 2016-03-24
 */
class MsgQHelper
{

    /**
     * 读取配置, 新建到MQ的连接
     * @param string $conf_key
     * @return AMQPChannel
     */
    protected static function getChannel($conf_key = 'jjb')
    {
        $mq_cfg = CFG::get("/msgq/$conf_key");
        $connection = new AMQPStreamConnection($mq_cfg['host'], $mq_cfg['port'], $mq_cfg['user'], $mq_cfg['password'], $mq_cfg['vhost'],
            /*insist=*/false,
            /*login_method=*/'AMQPLAIN',
            /*login_response=*/null,
            /*locale=*/'en_US',
            /*connection_timeout=*/3.0,
            /*read_write_timeout=*/3.0,
            /*context=*/null,
            /*keepalive=false*/true,
            /*heartbeat=*/0
        );
        return $connection->channel();
    }

    /**
     * @param AMQPChannel $channel
     * @return bool
     */
    protected static function isChannelValid($channel)
    {
        /**
         * stream_select
         * http://php.net/manual/zh/function.stream-select.php
         * 出错的时候返回 false
         * end-of-file 情况下返回 1
         */
        $result = $channel->getConnection()->select(0);
        //检测当前连接是否断开或者出错，并重连
        if ($result === false || $result === 1) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 将$body作为MQ包的内容publish出去.
     *
     * @param string $exchange_name
     * @param string $publish_routing_key
     * @param string $body
     * @param string $conf_key
     */
    public static function publishStr_old($exchange_name, $publish_routing_key, $body, $conf_key = 'jjb' /* 指定连接的MQ-server */)
    {
        static $channelArr = array();

        // 每一个<exchange,routing_key>组合对应一个独立的channel
        // >> 如果后端consumer没启动, 监听关系不存在的话 publish会报错
        // 然后这个channel上后续的publish都会报错.
        // 目前版本的PhpAmqpLib对publish错误没有及时抛异常. 对应有2种解决思路:
        // A. 使用confirm模式 不过这个对性能有一定消耗.
        // B. 开多个channel 能解决consumer没启动问题. 但是无法保证解决时期MQ失败问题
        $key = $conf_key . $exchange_name . $publish_routing_key;
        if (empty($channelArr[$key]) || !self::isChannelValid($channelArr[$key])) {
            $channelArr[$key] = self::getChannel($conf_key);
        }

        $msg = new AMQPMessage($body, array(
            "delivery_mode" => 2
        ));
        // "correlation_id" => "fake_CorrelationId",
        // "reply_to" => "fake_ReplyTo",

        $channelArr[$key]->basic_publish($msg, $exchange_name, $publish_routing_key);
    }

    /**
     * 读取配置, 新建到MQ的持久连接
     * @param string $conf_key
     * @return \AMQPConnection
     * @throws \AMQPConnectionException
     */
    private static function getPersistentConnection($conf_key, $force_reconnect = false) {
        /** @var $pConnectionArr \AMQPConnection[] */
        static $pConnectionArr = array();

        $mq_cfg = CFG::get("/msgq/$conf_key");
        if (empty($mq_cfg['login']) && !empty($mq_cfg['user'])) {
            $mq_cfg['login'] = $mq_cfg['user']; //兼容处理
        }

        /**
         * Persistent connection
         *   Limitations:
         *
         * 1. there may be only one persistent connection per unique credentials (login+password+host+port+vhost).
         * If there will be an attempt to create another persistent connection with the same credentials,
         * an exception will be thrown.
         *
         * 2. channels on persistent connections are not persistent: they are destroyed between requests.
         *
         * 3. heartbeats are limited to blocking calls only,
         * so if there are no any operations on a connection or no active consumer set,
         * connection may be closed by the broker as dead.
         *
         * Developers note: alternatively for built-in persistent connection support raphf pecl extension may be used.
         */
        $uniq_cred_key = md5(json_encode(array_intersect_key($mq_cfg, [
            'login' => null,
            'password' => null,
            'host' => null,
            'port' => null,
            'vhost' => null
        ])));

        if (!empty($pConnectionArr[$uniq_cred_key])) {
            if ($force_reconnect) {
                $pConnectionArr[$uniq_cred_key]->pdisconnect(); //强制重连
                unset($pConnectionArr[$uniq_cred_key]);
            } elseif ($pConnectionArr[$uniq_cred_key]->isConnected()) {
                return $pConnectionArr[$uniq_cred_key]; //连接有效, 直接返回
            } else {
                unset($pConnectionArr[$uniq_cred_key]);
            }
        }

        #$pConnectionArr[$uniq_cred_key] = new \AMQPConnection($mq_cfg);
        if ($force_reconnect) {
            $pConnectionArr[$uniq_cred_key]->preconnect(); //强制重连
        } else {
            $pConnectionArr[$uniq_cred_key]->pconnect();
        }

        return $pConnectionArr[$uniq_cred_key];
    }

    /**
     * 读取配置, 新建到MQ的持久连接, 再获取一个新channel
     * @param string $conf_key
     * @return \AMQPChannel
     * @throws \AMQPConnectionException
     */
    private static function newChannelOnPersistentConnection($conf_key) {
        /**
         * channel 每次创建新的, 这样顺便做了一次Persistent Connection有效性的检测
         *
        static $pChannelArr = array();

        if (!empty($pChannelArr[$conf_key])) {
            if ($pChannelArr[$conf_key]->isConnected()) {
                return $pChannelArr[$conf_key]; //channel有效, 直接返回
            } else {
                unset($pChannelArr[$conf_key]);
            }
        }
         */

        try {
            $pConn = self::getPersistentConnection($conf_key);
            $pCh = new \AMQPChannel($pConn);
        } catch (\AMQPException $ae) {
            LOG::error(sprintf('[%s] %s', get_class($ae), LOG::e2str($ae)));

            // 新建channel阶段出错后, 可以重试一次 (不会出现多丢消息情况)
            $pConn = self::getPersistentConnection($conf_key, true);
            $pCh = new \AMQPChannel($pConn);
        }

        return $pCh;
    }

    /**
     * 将$body作为MQ包的内容publish出去.
     *
     * @param string $exchange_name
     * @param string $publish_routing_key
     * @param string $body
     * @param string $conf_key
     *
     * @return bool
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public static function publishStr($exchange_name, $publish_routing_key, $body, $conf_key = 'jjb' /* 指定连接的MQ-server */)
    {
        $ch = self::newChannelOnPersistentConnection($conf_key);
        $ex = new \AMQPExchange($ch);

        $ex->setName($exchange_name);
        return $ex->publish($body, $publish_routing_key, AMQP_NOPARAM, array('delivery_mode' => AMQP_DURABLE));
    }

    public static function publishStr_nothrow($exchange_name, $publish_routing_key, $body, $conf_key = 'jjb' /* 指定连接的MQ-server */)
    {
        try {
            self::publishStr($exchange_name, $publish_routing_key, $body, $conf_key);
        } catch (\Exception $e) {
            LOG::error(LOG::e2str($e));
        }
    }

    /**
     * 使用 `确认模式` 将$body打包成MQ消息丢给 $conf_key对应的MQ服务
     *
     * @param string $exchange_name
     *            //若不存在,则抛出`NOT_FOUND`异常 //若是内部exchange,则抛出`ACCESS_REFUSED`异常 //refuse basic content,抛出`540 (not implemented)`异常
     * @param string $publish_routing_key
     *            //若无法命中路由规则,返回`returnInfo`
     * @param string $body
     * @param string $conf_key
     * @return string|array 处理结果:
     *         'ack' MQ消息接收成功且路由成功
     *         'nack' MQ消息接收失败
     *         array('replyCode' => 312, 'replyText' => "NO_ROUTE") MQ消息路由失败
     */
    public static function publishStrWithConfirm($exchange_name, $publish_routing_key, $body, $conf_key = 'jjb' /* 指定连接的MQ-server */)
    {
        static $channelWithConfirmArr = array();

        static $isAck = null;
        static $returnInfo = null;

        // key==conf_key即可, 到同一个broker只维持一个channel
        $key = $conf_key;
        if (empty($channelWithConfirmArr[$key]) || !self::isChannelValid($channelWithConfirmArr[$key])) {
            $channelWithConfirmArr[$key] = self::getChannel($key);
            $channel = $channelWithConfirmArr[$key];

            $channel->set_ack_handler(function ($message) use (&$isAck) {
                // echo "Message #" . $message->delivery_info['delivery_tag'] . " acked with content " . $message->body . PHP_EOL;
                $isAck = true;
            });

            $channel->set_nack_handler(function ($message) use (&$isAck) {
                // echo "Message #" . $message->delivery_info['delivery_tag'] . " nacked with content " . $message->body . PHP_EOL;
                // LOG::error(sprintf("MQ publish nacked: #%s", $message->delivery_info['delivery_tag']));
                $isAck = false;
            });

            $channel->set_return_listener(function ($replyCode, $replyText, $exchange, $routingKey, $message) use (&$returnInfo) {
                // echo "Message returned with content " . $message->body . PHP_EOL;
                // LOG::error(sprintf("MQ publish basic_return: %s:%s", $replyCode, $replyText));
                $returnInfo = array(
                    'replyCode' => $replyCode,
                    'replyText' => $replyText
                );
            });

            // bring the channel into publish confirm mode
            $channel->confirm_select();
        } else {
            $channel = $channelWithConfirmArr[$key];
        }

        $msg = new AMQPMessage($body, array(
            "delivery_mode" => 2
            // "correlation_id" => "fake_CorrelationId",
            // "reply_to" => "fake_ReplyTo",
        ));

        // publish msg
        $channel->basic_publish($msg, $exchange_name, $publish_routing_key, true);

        // [blocking] wait for ack/nack/return
        $channel->wait_for_pending_acks_returns();

        return empty($returnInfo) ? ($isAck ? 'ack' : 'nack') : $returnInfo;
    }

    public static function publishStrWithConfirm_nothrow($exchange_name, $publish_routing_key, $body, $conf_key = 'jjb' /* 指定连接的MQ-server */)
    {
        $ret = 'exception';
        try {
            $ret = self::publishStrWithConfirm($exchange_name, $publish_routing_key, $body, $conf_key);
        } catch (\Exception $e) {
            LOG::error(LOG::e2str($e));
        }
        return $ret;
    }

    /**
     * 监听MQ的队列, 将拿到的MQ消息包的内容作为$body参数, 调用 $procFunc() 进行业务逻辑处理.
     * 处理成功做ack, 抛异常做delayed-requeue.
     *
     * @param $procFunc
     * @param $exchange_name
     * @param $subscribe_routing_key
     * @param $subscribe_queue_name
     * @param bool $need_ack 是否使用ack模式
     * @param int $delayed_requeue_ms 延迟重试的等待时间
     * @param string $conf_key 指定连接的MQ-server的配置key
     * @param int $delayed_routing_ms 延迟路由到主队列的等待时间
     */
    public static function subscribeStr($procFunc /* 业务逻辑处理函数: void f($body) */,
                                        $exchange_name, $subscribe_routing_key, $subscribe_queue_name,
                                        $need_ack = true /* 默认是需要ack */, $delayed_requeue_ms = 30000 /* 30 sec */,
                                        $conf_key = 'jjb', $delayed_routing_ms = 0)
    {
        // declare(ticks = 100); //经测试, 新版php-amqplib不再需要定义ticks
        pcntl_async_signals(true); //PHP7.1后支持这种更高效的信号处理设置

        // 注册SIGINT,SIGTERM,SIGHUP的处理函数
        pcntl_signal(SIGINT, 'AiLeZai\Common\Lib\MsgQ\MsgQHelper__consumer_sigterm_func');
        pcntl_signal(SIGTERM, 'AiLeZai\Common\Lib\MsgQ\MsgQHelper__consumer_sigterm_func');
        pcntl_signal(SIGHUP, SIG_IGN);

        self::$channel = self::getChannel($conf_key);

        // 确保exchange, queue 以及 bind是正常的. {即使channel重连 consumer重启后, bind也保留}
        $subQueueName = self::setupBind(self::$channel, $exchange_name, $subscribe_routing_key, $subscribe_queue_name, $delayed_requeue_ms, $delayed_routing_ms);

        self::$flag_interrupted = false;
        while (! self::$flag_interrupted) {
            try {
                self::startConsumer(self::$channel, $subQueueName, $need_ack, $procFunc);
            } catch (\Exception $e) {
                LOG::error(LOG::e2str($e));

                self::$channel = self::getChannel($conf_key); // 重连
            }
        }
    }

    public static $flag_interrupted;

    /** @var AMQPChannel */
    public static $channel;

    public static $ctag;

    /**
     * @param AMQPChannel $channel
     * @param $exchange_name
     * @param $subscribe_routing_key
     * @param $subscribe_queue_name
     * @param $delayed_requeue_ms
     * @param int $delayed_routing_ms
     * @return string 创建成功的队列名
     */
    protected static function setupBind($channel, $exchange_name, $subscribe_routing_key, $subscribe_queue_name, $delayed_requeue_ms, $delayed_routing_ms)
    {
        // 初始化subscribe参数
        $channel->exchange_declare($exchange_name, 'topic', /*passive=*/false, /*durable=*/true, /*auto_delete=*/false);

        // 需要设置delayed-requeue
        if ($delayed_requeue_ms > 0) {
            $subQueueName = $subscribe_queue_name . '__dlx';
            $subDelayedQueueName = $subQueueName . '_requeue_' . $delayed_requeue_ms . 'ms';

            // 定义delayed队列
            // 配置Delayed队列的消息dead-lettered后{ttl超时}, 消息又回到主队列尾部
            $channel->queue_declare($subDelayedQueueName, /*passive=*/false, /*durable=*/true, /*exclusive=*/false, /*auto_delete=*/false,
                /*nowait=*/false, new AMQPTable(array(
                "x-message-ttl" => $delayed_requeue_ms,
                "x-dead-letter-exchange" => "",
                "x-dead-letter-routing-key" => $subQueueName
            )));
            // 队列队列默认都会自动以队列名做为routing_key绑定到default-exchange("")上
            // 所以publish("", queue_name)相当于直接将消息扔给指定队列

            // 定义主队列
            // 配置主队列的消息dead-lettered后{一般是reject(false)}, 消息路由到("", subDelayedQueueName)delayed队列.
            $channel->queue_declare($subQueueName, /*passive=*/false, /*durable=*/true, /*exclusive=*/false, /*auto_delete=*/false,
                /*nowait=*/false, new AMQPTable(array(
                "x-dead-letter-exchange" => "",
                "x-dead-letter-routing-key" => $subDelayedQueueName
            )));
        } else {
            $subQueueName = $subscribe_queue_name;
            $channel->queue_declare($subQueueName, /*passive=*/false, /*durable=*/true, /*exclusive=*/false, /*auto_delete=*/false);
        }

        // 需要设置delayed-routing
        if ($delayed_routing_ms > 0) {
            $targetQueue = $subQueueName . '_routing_' . $delayed_routing_ms . 'ms';

            // 定义delayed队列
            // 配置Delayed队列的消息dead-lettered后{ttl超时}, 消息路由到主队列尾部
            $channel->queue_declare($targetQueue, /*passive=*/false, /*durable=*/true, /*exclusive=*/false, /*auto_delete=*/false,
                /*nowait=*/false, new AMQPTable(array(
                    "x-message-ttl" => $delayed_routing_ms,
                    "x-dead-letter-exchange" => "",
                    "x-dead-letter-routing-key" => $subQueueName
                )));
            // 队列队列默认都会自动以队列名做为routing_key绑定到default-exchange("")上
            // 所以publish("", queue_name)相当于直接将消息扔给指定队列
        } else {
            $targetQueue = $subQueueName;
        }

        $channel->queue_bind($targetQueue, $exchange_name, $subscribe_routing_key);

        return $subQueueName;
    }

    /**
     * @param AMQPChannel $channel
     * @param $subQueueName
     * @param $need_ack
     * @param $procFunc
     */
    protected static function startConsumer($channel, $subQueueName, $need_ack, $procFunc)
    {
        // 预取10个消息包
        $channel->basic_qos(0, 10, false);

        // 启动consumer循环
        self::$ctag = $channel->basic_consume($subQueueName, /*上次的consumerTag*/"",
            /*no_local=*/false, /*no_ack=*/! $need_ack, /*exclusive=*/false, /*nowait=*/false, function ($message) use ($channel, $need_ack, $procFunc) {
            /** @var AMQPMessage $message */

            $_startTime = microtime(true);
            $__fn_stat = new ScopeExit(function () use ($_startTime) {
                TaskHistogram::get()->stat((microtime(true) - $_startTime) * 1000);
            });

            try {
                call_user_func($procFunc, $message->body); // 不抛异常则认为是处理成功

                if ($need_ack) {
                    $channel->basic_ack($message->delivery_info['delivery_tag']); // ack
                }
            } catch (\Exception $e) {
                LOG::error(LOG::e2str($e));
                if ($need_ack) {
                    // 设置reason=`rejected`并将消息扔掉, 若配置了dlx则效果是绕一圈重新入队尾
                    $channel->basic_reject($message->delivery_info['delivery_tag'], false); // delayed_requeue
                }
            }

            // $procFunc执行完后, 应该将日志的traceId重置成当前的consumer_tag
            LOG::setTraceId(MsgQHelper::$ctag);

            // 尽量让多余的内存都释放掉
            gc_collect_cycles();
        });

        TaskHistogram::get()->l_task($subQueueName, true);

        // consumer启动成功后, 将日志的traceId设置成当前的consumer_tag
        LOG::setTraceId(self::$ctag);
        LOG::info(sprintf('consumer[%s] started on queue[%s], procFunc[%s]',
            MsgQHelper::$ctag, $subQueueName, LOG::varToString($procFunc)));

        while (isset($channel->callbacks[self::$ctag]) && $channel->getConnection()->select(null)) {
            $channel->wait(); // 这个wait蛮难理解的, 有点类似libevent的loop()
        }
    }
}

function MsgQHelper__consumer_sigterm_func($sig)
{
    MsgQHelper::$flag_interrupted = true;
    echo (sprintf('tag[%s] got sig[%d]', MsgQHelper::$ctag, $sig)) . "\n";
    LOG::info(sprintf('consumer[%s] got signal[%d]', MsgQHelper::$ctag, $sig));
    MsgQHelper::$channel->basic_cancel(MsgQHelper::$ctag, false, true); // 这里会保证wait()操作break出来
}
