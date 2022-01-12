<?php

namespace AiLeZai\Common\Lib\Prometheus\Application;

use AiLeZai\Common\Lib\Prometheus\BaseStaticHistogram;

/**
 * Class ReqRespHistogram
 * @package AiLeZai\Common\Lib\Prometheus\Application
 *
 * @method ReqRespHistogram l_uri(string $v)       请求的"controller/action"
 * @method ReqRespHistogram l_os(string $v)        客户端类型: ios android h5
 * @method ReqRespHistogram l_version(string $v)   客户端版本号
 * @method ReqRespHistogram l_channel(string $v)   客户端渠道包名
 * @method ReqRespHistogram l_errcode(string $v)   响应给客户端的错误码
 */
class ReqRespHistogram extends BaseStaticHistogram
{
    /**
     * @override
     */
    const SCHEMA_VER = 'v2018.06.10';

    /**
     * @override
     */
    const TYPE = 'req_resp';

    /**
     * @override
     * // 上报时必须保证每个key都有值, 没有值默认填 `_`
     */
    const DEFAULT_LABELS = array(
        'uri' => '_',
        //'os' => '_',
        //'version' => '_',
        //'channel' => '_',
        //'errcode' => '_',
    );

    /**
     * @override
     * //梯度有点类似 人民币面值 设定... 1,2,5
     */
    const DEFAULT_BUCKETS = array(
        1,  //qps=1000
        5,  //qps=200
        10, //qps=100
        20, //qps=50
        50, //qps=20
        100, //qps=10
        200, //qps=5
        500, //qps=2
        1000, //1秒
        3000, //3秒
        10000, //10秒
    );

    /**
     * @var static[]
     */
    protected static $instanceMap = array();
    protected static $currentCategory = '_';
}
