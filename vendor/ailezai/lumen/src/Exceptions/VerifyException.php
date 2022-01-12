<?php

namespace AiLeZai\Lumen\Framework\Exceptions;

class VerifyException extends \Exception implements CustomException
{
    /**
     * admin项目，ajax校验失败
     *
     * @return \Illuminate\Http\Response
     */
    public function ajaxExceptionResponse()
    {
        return ajax_response()->ajaxFailureResponse($this->getMessage());
    }

    /**
     * admin项目，校验失败
     *
     * @return \Illuminate\Http\Response
     */
    public function adminExceptionResponse()
    {
        $view = view('errors.tip')
            ->with('msg', $this->getMessage());

        return response($view, 404);
    }

    /**
     * api项目，请求校验失败
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