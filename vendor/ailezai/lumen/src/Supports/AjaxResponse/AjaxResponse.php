<?php

namespace AiLeZai\Lumen\Framework\Supports\AjaxResponse;

class AjaxResponse
{
    /**
     * ajax请求返回
     *
     * @param int $code
     * @param string $msg
     * @param array $data
     * @param string $type
     * @param string $url
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ajaxResponse($code = 200, $msg = '', $data = [], $type = '', $url = '')
    {
        return response()->json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'type' => $type,
            'url'  => $url,
        ]);
    }

    /**
     * ajax请求成功返回
     *
     * @param string $msg
     * @param array $data
     * @param string $type
     * @param string $url
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxSuccessResponse($msg = '', $data = [], $type = '', $url = '')
    {
        return $this->ajaxResponse(200, $msg, $data, $type, $url);
    }

    /**
     * ajax请求失败返回
     *
     * @param string $msg
     * @param array $data
     * @param string $type
     * @param string $url
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxFailureResponse($msg = '', $data = [], $type = '', $url = '')
    {
        return $this->ajaxResponse(500, $msg, $data, $type, $url);
    }

    /**
     * ajax登录成功返回
     *
     * @param string $url
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxLoginSuccessResponse($url = '')
    {
        return $this->ajaxResponse(200, '登录成功', [], 'redirect', $url);
    }

    /**
     * ajax登录失败返回
     *
     * @param string $msg
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxLoginFailureResponse($msg = '')
    {
        return $this->ajaxResponse(1401, '登录失败 ' . $msg, [], 'reload_captcha_code');
    }

    /**
     * ajax登录过期返回
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxLoginExpiredResponse()
    {
        return $this->ajaxResponse(300, '登录超时，请重新登录', [], 'redirect', url('login'));
    }

    /**
     * ajax没有权限返回
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxNoAuthResponse()
    {
        return $this->ajaxResponse(503, '没有权限', [], 'redirect', url('login'));
    }
}