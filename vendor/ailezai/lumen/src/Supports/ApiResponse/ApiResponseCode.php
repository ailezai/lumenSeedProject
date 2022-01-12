<?php

namespace AiLeZai\Lumen\Framework\Supports\ApiResponse;

class ApiResponseCode
{
    /**
     * @var int 默认成功响应码
     */
    const DEFAULT_SUCCESS_ERRORCODE = 0;

    /**
     * @var int 默认失败响应码
     */
    const DEFAULT_FAIL_ERRORCODE = 1;

    /**
     * @var string 默认成功响应文案
     */
    const DEFAULT_SUCCESS_ERRORSTR = 'ok';
}