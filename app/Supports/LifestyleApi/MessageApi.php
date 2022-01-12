<?php

namespace App\Supports\LifestyleApi;


class MessageApi
{
    /**
     * 事件配置开关
     *
     * @param int $eventId
     * @param string $switchStatus
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function sendMiniappMsg($data)
    {
        return JavaHttpClient::call('lifestyle-api', 'message/sendMiniappMsg',$data,'POST','json');
    }
}