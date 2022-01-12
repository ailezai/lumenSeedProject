<?php

namespace AiLeZai\Lumen\Framework\Exceptions;

class ApiException extends \Exception implements CustomException
{
    /**
     * api项目不存在ajax请求
     *
     * @return \Illuminate\Http\Response
     */
    public function ajaxExceptionResponse()
    {
        // 不需要处理
    }

    /**
     * api项目不存在视图返回
     *
     * @return \Illuminate\Http\Response
     */
    public function adminExceptionResponse()
    {
        // 不需要处理
    }

    /**
     * api项目，请求出现异常
     *
     * @return \Illuminate\Http\Response
     */
    public function apiExceptionResponse()
    {
        return api_response()->failOnlyWithMessageResponse(
            $this->getCode(),
            $this->getMessage()
        );
    }
}