<?php

namespace AiLeZai\Lumen\Framework\Exceptions;

interface CustomException
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function ajaxExceptionResponse();

    /**
     * @return \Illuminate\Http\Response
     */
    public function adminExceptionResponse();

    /**
     * @return \Illuminate\Http\Response
     */
    public function apiExceptionResponse();
}