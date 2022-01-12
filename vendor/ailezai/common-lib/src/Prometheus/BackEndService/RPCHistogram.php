<?php
namespace AiLeZai\Common\Lib\Prometheus\BackEndService;

use AiLeZai\Common\Lib\Prometheus\BaseStaticHistogram;

/**
 * Class RPCHistogram
 * @package AiLeZai\Common\Lib\Prometheus\BackEndService
 *
 * @method RPCHistogram l_from(string $v)      发起调用的`uri`或`task`
 * @method RPCHistogram l_service(string $v)   调用的服务名
 * @method RPCHistogram l_if(string $v)        调用的接口名
 * @method RPCHistogram l_errcode(string $v)   调用的返回码: '0'表示成功, '-1'表示无code的异常
 */
class RPCHistogram extends BaseStaticHistogram
{
    /**
     * @override
     */
    const SCHEMA_VER = 'v2018.06.10';

    /**
     * @override
     */
    const TYPE = 'rpc';

    /**
     * @override
     * // 上报时必须保证每个key都有值, 没有值默认填 `_`
     */
    const DEFAULT_LABELS = array(
        //'from' => '_',
        //'service' => '_',
        'if' => '_',
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

    /**
     * @param \Exception $e
     * @return static
     */
    public function parsePHPException($e)
    {
        // TODO 将各种异常子类分别判断... 提取更有效的错误码

        // 非0的code 才追加到errcode; 其他情况统一设置-1
        $errcode = empty($e->getCode()) ? -1 : $e->getCode();
        return $this->l_errcode($errcode);
    }
}
