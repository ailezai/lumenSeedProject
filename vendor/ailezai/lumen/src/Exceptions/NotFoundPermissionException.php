<?php

namespace AiLeZai\Lumen\Framework\Exceptions;

class NotFoundPermissionException extends \Exception implements CustomException
{
    /**
     * admin项目，ajax请求没有权限
     *
     * @return \Illuminate\Http\Response
     */
    public function ajaxExceptionResponse()
    {
        return ajax_response()->ajaxFailureResponse($this->getMessage());
    }

    /**
     * admin项目，请求没有权限
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
     * api项目，请求没有权限
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