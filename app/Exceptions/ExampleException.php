<?php

namespace App\Exceptions;

use AiLeZai\Lumen\Framework\Exceptions\CustomException;

class ExampleException extends \Exception implements CustomException
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function ajaxExceptionResponse()
    {
        // TODO: Implement ajaxExceptionResponse() method.
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function adminExceptionResponse()
    {
        // TODO: Implement adminExceptionResponse() method.
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function apiExceptionResponse()
    {
        // TODO: Implement apiExceptionResponse() method.
    }
}