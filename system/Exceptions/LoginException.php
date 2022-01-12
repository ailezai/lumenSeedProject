<?php

namespace System\Exceptions;

use AiLeZai\Lumen\Framework\Exceptions\CustomException;

class LoginException extends \Exception implements CustomException
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function ajaxExceptionResponse()
    {
        return ajax_response()->ajaxFailureResponse($this->getMessage(), [], 'reload_captcha_code');
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function adminExceptionResponse()
    {
        $view = view('login')
            ->with('msg', $this->getMessage());

        return response($view, 500);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function apiExceptionResponse()
    {
        // TODO: Implement apiExceptionResponse() method.
    }
}