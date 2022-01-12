<?php

namespace AiLeZai\Lumen\Framework\Exceptions;

use Illuminate\Validation\ValidationException as BaseValidationException;

class ValidationException extends BaseValidationException implements CustomException
{
    /**
     * admin项目，ajax请求验证参数不通过
     *
     * @return \Illuminate\Http\Response
     */
    public function ajaxExceptionResponse()
    {
        return ajax_response()->ajaxFailureResponse($this->getResponse());
    }

    /**
     * admin项目，请求验证参数不通过
     *
     * @return \Illuminate\Http\Response
     */
    public function adminExceptionResponse()
    {
        $this->message = $this->response;

        $view = view('errors.tip')
            ->with('msg', $this->getMessage());

        return response($view, 404);
    }

    /**
     * api项目，请求验证参数不通过
     *
     * @return \Illuminate\Http\Response
     */
    public function apiExceptionResponse()
    {
        return api_response()->validationFailOnlyWithMessageResponse($this->getResponse());
    }
}