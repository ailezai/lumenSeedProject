<?php

namespace AiLeZai\Lumen\Framework\Exceptions;

class ServerErrorException extends \Exception implements CustomException
{
    /**
     * admin项目调用server，ajax请求出错
     *
     * @return \Illuminate\Http\Response
     */
    public function ajaxExceptionResponse()
    {
        return ajax_response()->ajaxFailureResponse($this->getMessage());
    }

    /**
     * admin项目调用server，请求出错
     *
     * @return \Illuminate\Http\Response
     */
    public function adminExceptionResponse()
    {
        $view = view('errors.500')
            ->with('msg', $this->getMessage());

        return response($view, 500);
    }

    /**
     * api项目调用server，请求出错
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