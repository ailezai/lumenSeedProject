<?php

namespace AiLeZai\Common\Lib\Prometheus\BackEndService;

use AiLeZai\Common\Lib\Prometheus\BaseStaticHistogram;

/**
 * Class DBHistogram
 * @package AiLeZai\Common\Lib\Prometheus\BackEndService
 *
 * @method DBHistogram l_from(string $v)      发起DB操作的`uri`或`task`
 * @method DBHistogram l_rds(string $v)       操作的数据库实例名
 * @method DBHistogram l_table(string $v)     操作的表名
 * @method DBHistogram l_action(string $v)    操作类型: select, update...
 */
class DBHistogram extends BaseStaticHistogram
{
    /**
     * @override
     */
    const SCHEMA_VER = 'v2018.06.10';

    /**
     * @override
     */
    const TYPE = 'db';

    /**
     * @override
     * // 上报时必须保证每个key都有值, 没有值默认填 `_`
     */
    const DEFAULT_LABELS = array(
        //'from' => '_',
        //'rds' => '_',
        'table' => '_',
        'action' => '_',
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
