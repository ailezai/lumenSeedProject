<?php

namespace AiLeZai\Lumen\Framework\Exceptions;

class WebException extends \Exception implements CustomException
{
    /**
     * admin项目，ajax请求异常
     *
     * @return \Illuminate\Http\Response
     */
    public function ajaxExceptionResponse()
    {
        return ajax_response()->ajaxFailureResponse($this->getMessage());
    }

    /**
     * admin项目，视图请求异常
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
     * admin项目不存在接口返回
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