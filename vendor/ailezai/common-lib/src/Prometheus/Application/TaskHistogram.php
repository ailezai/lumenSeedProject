<?php

namespace AiLeZai\Common\Lib\Prometheus\Application;

use AiLeZai\Common\Lib\Prometheus\BaseStaticHistogram;

/**
 * Class TaskHistogram
 * @package AiLeZai\Common\Lib\Prometheus\Application
 *
 * @method TaskHistogram l_task(string $v, bool $set_once)      当前脚本或任务名
 * // MQ脚本用队列名, 或提前设置名称
 */
class TaskHistogram extends BaseStaticHistogram
{
    /**
     * @override
     */
    const SCHEMA_VER = 'v2018.06.05';

    /**
     * @override
     */
    const TYPE = 'task';

    /**
     * @override
     * // 上报时必须保证每个key都有值, 没有值默认填 `_`
     */
    const DEFAULT_LABELS = array(
        'task' => '_',
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
