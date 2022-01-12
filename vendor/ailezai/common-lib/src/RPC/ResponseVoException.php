<?php

namespace AiLeZai\Common\Lib\RPC;

class ResponseVoException extends \Exception
{
    protected $resultCode;
    protected $errorCode;
    protected $errorDesc;

    protected $result;

    public function __construct($responseVo, $codePrefix = "18089")
    {
        $this->resultCode = $responseVo['resultCode'];
        $this->errorCode = $responseVo['errorCode'];
        $this->errorDesc = $responseVo['errorDesc'];

        $this->result = $responseVo['result'];

        $message = sprintf("responseVo[%s,%s,%s] error!", $this->resultCode, $this->errorCode, $this->errorDesc);
        // 上层catch到$e后, 用$e->getCode()提取出 `经过转换处理的` 底层服务`errorCode`
        $code = intval($codePrefix . preg_replace('/\D/', '', $this->errorCode));

        parent::__construct($message, $code);
    }

    public function getResultCode()
    {
        return $this->resultCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorDesc()
    {
        return $this->errorDesc;
    }

    public function getResult()
    {
        return $this->result;
    }
}
