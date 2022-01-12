<?php

namespace AiLeZai\Lumen\Framework\Supports\ApiResponse;

use Illuminate\Http\Response;

interface ApiResponseFactory
{
    /**
     * @var int 默认成功响应码
     */
    const DEFAULT_RESPONSE_SUCCESS_ERRORCODE = 0;

    /**
     * @var int 默认失败响应码
     */
    const DEFAULT_RESPONSE_FAIL_ERRORCODE = 1;

    /**
     * @var int 默认验证参数失败的失败响应码
     */
    const DEFAULT_RESPONSE_VALIDATION_FAIL_ERRORCODE = 1422;

    /**
     * @var string 默认成功响应文案
     */
    const DEFAULT_RESPONSE_SUCCESS_ERRORSTR = 'ok';

    /**
     * @var string 默认验证参数失败的失败响应文案
     */
    const DEFAULT_RESPONSE_VALIDATION_FAIL_ERRORSTR = '请求参数错误';

    /**
     * @var int 默认出错响应码
     */
    const DEFAULT_ERROR_RESPONSE_ERRORCODE = 500;

    /**
     * @var string 默认出错响应文案
     */
    const DEFAULT_ERROR_RESPONSE_ERRORSTR = '未知错误';

    /**
     * 返回一个成功的响应
     *
     * @param string $message 响应信息
     * @param array|\Illuminate\Support\Collection $data 响应数据
     * @param int $endMark 结束标志
     *
     * @return Response
     */
    public function successResponse($message = '', $data = null, $endMark = 0);

    /**
     * 返回一个错误的响应
     *
     * @param int $code 错误响应码
     * @param string $message 响应信息
     * @param array|\Illuminate\Support\Collection $data 响应数据
     *
     * @return Response
     */
    public function failResponse($code, $message = '', $data = null);

    /**
     * 返回一个只携带数据的成功响应
     *
     * @param array|\Illuminate\Support\Collection $data 响应数据
     * @param int $endMark 结束标志
     *
     * @return Response
     */
    public function successOnlyWithDataResponse($data, $endMark = 0);

    /**
     * 返回一个只携带信息的成功响应
     *
     * @param string $message 响应信息
     *
     * @return Response
     */
    public function successOnlyWithMessageResponse($message);

    /**
     * 返回一个只携带信息的失败响应
     *
     * @param int $code 响应码
     * @param string $message 响应信息
     *
     * @return Response
     */
    public function failOnlyWithMessageResponse($code, $message);

    /**
     * 返回成功响应
     *
     * @return Response
     */
    public function onlySuccessResponse();

    /**
     * 验证参数失败返回一个错误的响应
     *
     * @return Response
     */
    public function validationFailResponse();

    /**
     * 验证参数失败返回一个只携带信息的错误响应
     *
     * @param string $message
     *
     * @return Response
     */
    public function validationFailOnlyWithMessageResponse($message);

    /**
     * 返回一个请求出错的响应
     *
     * @param \Exception $exception
     *
     * @return Response
     */
    public function errorResponse(\Exception $exception);

}