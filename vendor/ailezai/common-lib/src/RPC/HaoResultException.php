<?php

namespace AiLeZai\Common\Lib\RPC;

class HaoResultException extends \Exception
{
    /**
     * @var HaoApi\HaoResult
     */
    public $haoResult;

    /**
     * HaoResultException constructor.
     * @param HaoApi\HaoResult $haoResult
     */
    public function __construct(HaoApi\HaoResult $haoResult)
    {
        $this->haoResult = $haoResult;

        // \Exception构造只允许传入int值的错误码
        parent::__construct($haoResult->errorStr, intval($haoResult->errorCode));
    }
}
