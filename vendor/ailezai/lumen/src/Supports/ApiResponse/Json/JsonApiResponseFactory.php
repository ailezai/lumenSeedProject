<?php

namespace AiLeZai\Lumen\Framework\Supports\ApiResponse\Json;

use AiLeZai\Common\Lib\Jwt\Api\JwtHelper;
use AiLeZai\Lumen\Framework\Supports\ApiResponse\ApiResponseCode;
use AiLeZai\Lumen\Framework\Supports\ApiResponse\ApiResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

class JsonApiResponseFactory extends ApiResponseCode implements ApiResponseFactory
{
    /**
     * 构件响应数据
     *
     * @param array|\Illuminate\Support\Collection $data
     *
     * @return array
     */
    private function buildResponseData($data)
    {
        if (is_null($data)) {
            return [];
        }

        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof Collection) {
            return $data->toArray();
        }

        if ($data instanceof AbstractPaginator) {
            return $data->toArray();
        }

        return [];
    }

    /**
     * @param int $code
     * @param string $message
     * @param array|\Illuminate\Support\Collection $data
     * @param int $endMark
     *
     * @return array
     */
    private function buildJsonData($code, $message = '', $data = null, $endMark = 0)
    {
        $data = $this->buildResponseData($data);
        return [
            'errorCode'     =>  $code,
            'errorStr'      =>  $message,
            'resultCount'   =>  count($data),
            'results'       =>  $data,
            'endMark'       =>  $endMark,
            'token'         =>  JwtHelper::getRespToken()
        ];
    }

    /**
     * 返回一个成功的响应
     *
     * @param string $message 响应信息
     * @param array|\Illuminate\Support\Collection $data 响应数据
     * @param int $endMark 结束标志
     *
     * @return Response
     */
    public function successResponse($message = '', $data = null, $endMark = 0)
    {
//        api_log_success($this->buildResponseData($data));
        if (empty($message)) {
            $message = static::DEFAULT_SUCCESS_ERRORSTR;
        }
        return response()->json(
            $this->buildJsonData(
                static::DEFAULT_SUCCESS_ERRORCODE,
                $message,
                $data,
                $endMark
            )
        );
    }

    /**
     * 返回一个错误的响应
     *
     * @param int $code 错误响应码
     * @param string $message 响应信息
     * @param array|\Illuminate\Support\Collection $data 响应数据
     *
     * @return Response
     */
    public function failResponse($code, $message = '', $data = null)
    {
//        api_log_fail($this->buildResponseData($data));
        $code = ($code != 0) ? $code: static::DEFAULT_RESPONSE_FAIL_ERRORCODE;
        return response()->json(
            $this->buildJsonData($code, $message, $data)
        );
    }

    /**
     * 返回一个只携带数据的成功响应
     *
     * @param array|\Illuminate\Support\Collection $data 响应数据
     * @param int $endMark 结束标志
     *
     * @return Response
     */
    public function successOnlyWithDataResponse($data, $endMark = 0)
    {
//        api_log_success($this->buildResponseData($data));
        return response()->json(
            $this->buildJsonData(
                static::DEFAULT_SUCCESS_ERRORCODE,
                static::DEFAULT_SUCCESS_ERRORSTR,
                $data,
                $endMark
            )
        );
    }

    /**
     * 返回一个只携带信息的成功响应
     *
     * @param string $message 响应信息
     *
     * @return Response
     */
    public function successOnlyWithMessageResponse($message)
    {
//        api_log_success([static::DEFAULT_SUCCESS_ERRORSTR]);
        return response()->json(
            $this->buildJsonData(
                static::DEFAULT_SUCCESS_ERRORCODE,
                $message
            )
        );
    }

    /**
     * 返回一个只携带信息的失败响应
     *
     * @param int $code 响应码
     * @param string $message 响应信息
     *
     * @return Response
     */
    public function failOnlyWithMessageResponse($code, $message)
    {
//        api_log_fail([$message]);
        $code = ($code != 0) ? $code: static::DEFAULT_RESPONSE_FAIL_ERRORCODE;
        return response()->json(
            $this->buildJsonData($code, $message)
        );
    }

    /**
     * 返回成功响应
     *
     * @return Response
     */
    public function onlySuccessResponse()
    {
//        api_log_success([static::DEFAULT_SUCCESS_ERRORSTR]);
        return response()->json(
            $this->buildJsonData(
                static::DEFAULT_SUCCESS_ERRORCODE,
                static::DEFAULT_SUCCESS_ERRORSTR
            )
        );
    }

    /**
     * 验证参数失败返回一个错误的响应
     *
     * @return Response
     */
    public function validationFailResponse()
    {
//        api_log_fail([static::DEFAULT_RESPONSE_VALIDATION_FAIL_ERRORSTR]);
        return response()->json(
            $this->buildJsonData(
                static::DEFAULT_RESPONSE_VALIDATION_FAIL_ERRORCODE,
                static::DEFAULT_RESPONSE_VALIDATION_FAIL_ERRORSTR
            )
        );
    }

    /**
     * 验证参数失败返回一个只携带信息的错误响应
     *
     * @param string $message
     *
     * @return Response
     */
    public function validationFailOnlyWithMessageResponse($message)
    {
//        api_log_fail([$message]);
        return response()->json(
            $this->buildJsonData(
                static::DEFAULT_RESPONSE_VALIDATION_FAIL_ERRORCODE,
                $message
            )
        );
    }

    /**
     * 返回一个请求出错的响应
     *
     * @param \Exception $exception
     *
     * @return Response
     */
    public function errorResponse(\Exception $exception)
    {
//        api_log_error($exception->getTrace());
        return response()->json(
            $this->buildJsonData(
                static::DEFAULT_ERROR_RESPONSE_ERRORCODE,
                static::DEFAULT_ERROR_RESPONSE_ERRORSTR
            )
        );
    }
}