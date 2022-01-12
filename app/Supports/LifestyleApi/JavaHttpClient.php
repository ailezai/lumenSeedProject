<?php

/**
 * Created by PhpStorm.
 * User: 陈思池 <925.andrewchan@gmail.com>
 * Time: 31/08/2018 3:43 PM
 */

namespace App\Supports\LifestyleApi;

use GuzzleHttp\Client;
use AiLeZai\Common\Lib\Config\CFG;
use AiLeZai\Common\Lib\Jwt\Api\JwtHelper;
use AiLeZai\Common\Lib\Log\LOG;
use AiLeZai\Common\Lib\Log\ReqRespLogBuilder;

class JavaHttpClient
{
    private static function getClient($clientName, $forceNew = false)
    {
        static $instanceArr = array();
        // 使用静态变量, 同一请求上下文 复用$client对象
        if ($forceNew || empty($instanceArr[$clientName])) {
            $instanceArr[$clientName] = new Client(array(
                'base_uri' => CFG::get("/java-request/$clientName/host"),
                'connect_timeout' => CFG::get("/java-request/$clientName/connect_timeout", 3.0),
                'timeout' => CFG::get("/java-request/$clientName/timeout", 10.0),
                'http_errors' => false, // 4xx,5xx时不抛异常
            ));
        }

        return $instanceArr[$clientName];
    }

    private static function buildReqHeaders()
    {
        // 默认一定包含 traceId
        $headers = array(
            'X-B3-TraceId' => LOG::getTraceId(),
            'password'=>'21ef054c446b51e84b2cf2090c1857ea',
        );

        // 若有jwt头, 透传到底层服务
        JwtHelper::addPassThroughAuthHeader($headers);

        return $headers;
    }

    /**
     * @param string $clientName
     * @param string $urlPath
     * @param array $params
     * @param string $method
     * @param string $contentType
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function call($clientName, $urlPath, $params, $method = 'POST', $contentType = 'json')
    {
        $reqRespLog = new ReqRespLogBuilder($clientName, $clientName, $urlPath, $params);
        try {
            $output = self::getClient($clientName)
                ->request($method, $urlPath, array(
                    $contentType => $params,
                    'headers' => self::buildReqHeaders(),
                ))
                ->getBody()
                ->getContents();
            $reqRespLog->respOk($output);
            return $output;
        } catch (\Exception $exception) {
            $reqRespLog->respError($exception);
            throw $exception;
        }
    }


}
