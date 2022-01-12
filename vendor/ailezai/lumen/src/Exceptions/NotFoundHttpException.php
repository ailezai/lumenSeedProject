<?php

namespace AiLeZai\Lumen\Framework\Exceptions;

use \Symfony\Component\HttpKernel\Exception\NotFoundHttpException as BaseNotFoundHttpException;

class NotFoundHttpException extends BaseNotFoundHttpException implements CustomException
{
    /**
     * admin项目，ajax请求不存在路由
     *
     * @return \Illuminate\Http\Response
     */
    public function ajaxExceptionResponse()
    {
        return ajax_response()->ajaxFailureResponse('请求的链接不存在');
    }

    /**
     * admin项目，请求不存在路由
     *
     * @return \Illuminate\Http\Response
     */
    public function adminExceptionResponse()
    {
        $view = view('errors.404')
            ->with('msg', '请求的链接不存在');

        return response($view, 404);
    }

    /**
     * admin项目，请求不存在路由
     *
     * @return \Illuminate\Http\Response
     */
    public function apiExceptionResponse()
    {
        return api_response()->failOnlyWithMessageResponse(
            1404,
            '请求的接口不存在'
        );
    }
}