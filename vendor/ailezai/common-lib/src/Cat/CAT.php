<?php

namespace AiLeZai\Common\Lib\Cat;

use AiLeZai\Common\Lib\MsgQ\MsgQHelper;

class CAT
{
    protected static $msgBody;

    protected static $msgData;

    /**
     * @param string $type
     * @param string $name
     * @param string $alias
     * @param string|array $value
     * @param string $status
     * @param string $time
     * @param int $runtime
     *
     * @return array
     */
    public static function fillDataTemplate($type, $name, $alias, $value, $status, $time, $runtime)
    {
        return array(
            "type" => $type,
            "name" => $name,
            "alias" => $alias,
            "value" => $value,
            "status" => $status,
            "time" => $time,
            "runtime" => $runtime,
        );
    }

    /**
     * @param array $transaction
     * @param array $event
     * @param array $header
     * @param string $ip
     * @param string $domain
     *
     * @return array
     */
    public static function fillMsgDataTemplate($transaction = array(), $event = array(), $header = array(), $ip, $domain)
    {
        return array(
            'transaction' => $transaction,
            'event' => $event,
            'header' => $header,
            'ip' => $ip,
            'domain' => $domain
        );
    }

    public static function buildMsgData($msgData)
    {
        static::$msgData = $msgData;
    }

    public static function getMsgBody()
    {
        return array('messageObject' => static::$msgData);
    }

    public static function sendMsg()
    {
        $body = json_encode(static::getMsgBody());
        MsgQHelper::publishStr_nothrow("phpcat.exchange", "phpcat_send", $body, 'mq_cluster');
    }
}
