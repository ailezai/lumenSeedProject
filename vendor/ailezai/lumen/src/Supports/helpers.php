<?php

use GuzzleHttp\Exception\GuzzleException;
use AiLeZai\Lumen\Framework\Exceptions\ServerErrorException;

if (!function_exists('api_response')) {

    /**
     * get api response instance.
     *
     * @return \AiLeZai\Lumen\Framework\Supports\ApiResponse\ApiResponseFactory
     */
    function api_response()
    {
        return app()->make(\AiLeZai\Lumen\Framework\Supports\ApiResponse\ApiResponseFactory::class);
    }
}

if (!function_exists('ajax_response')) {

    /**
     * get ajax response instance.
     *
     * @return \AiLeZai\Lumen\Framework\Supports\AjaxResponse\AjaxResponse
     */
    function ajax_response()
    {
        return app()->make(\AiLeZai\Lumen\Framework\Supports\AjaxResponse\AjaxResponse::class);
    }
}

if (! function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('session');
        }

        if (is_array($key)) {
            return app('session')->put($key);
        }

        return app('session')->get($key, $default);
    }
}

if (! function_exists('redis')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  dynamic  key|key,default|data,expiration|null
     * @return mixed
     *
     * @throws \Exception
     */
    function redis()
    {
        return app('redis');
    }
}

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  dynamic  key|key,default|data,expiration|null
     * @return mixed
     *
     * @throws \Exception
     */
    function cache()
    {
        $arguments = func_get_args();

        if (empty($arguments)) {
            return app('cache');
        }

        if (is_string($arguments[0])) {
            return app('cache')->get($arguments[0], isset($arguments[1]) ? $arguments[1] : null);
        }

        if (! is_array($arguments[0])) {
            throw new Exception(
                'When setting a value in the cache, you must pass an array of key / value pairs.'
            );
        }

        if (! isset($arguments[1])) {
            throw new Exception(
                'You must specify an expiration time when setting a value in the cache.'
            );
        }

        return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1]);
    }
}

if (!function_exists('asset')) {

    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    function asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}

if (!function_exists('invoke_server')) {

    /**
     * 调用微服务
     *
     * @param string $url
     * @param string $method
     * @param array $options
     * @param string $serverUrl
     *
     * @return array
     *
     * @throws GuzzleException
     * @throws ServerErrorException
     */
    function invoke_server($url, $method = 'get', array $options = [], $serverUrl = '')
    {
        if (empty($serverUrl)) {
            if (empty(env('SERVER_URL'))) {
                throw new ServerErrorException(
                    'config server error',
                    500
                );
            } else {
                $serverUrl = env('SERVER_URL');
            }
        }

        // 服务调用，传递 traceId
        $clientTraceId = \AiLeZai\Common\Lib\Log\LOG::getTraceId();
        if (!empty($options['headers'])) {
            $options['headers']['X-Trace-Id'] = $clientTraceId;
        } else {
            $options['headers'] = [
                'X-Trace-Id' => $clientTraceId
            ];
        }

        try {

            $client = new \GuzzleHttp\Client();
            $response = $client
                ->request($method, $serverUrl . $url, $options)
                ->getBody()
                ->getContents();

            $resp = json_decode($response, true);

            if ($resp['errorCode'] != 0) {
                throw new \AiLeZai\Lumen\Framework\Exceptions\ServerApiException(
                    $resp['errorStr'],
                    $resp['errorCode']
                );
            }

            return $resp['results'];

        } catch (\GuzzleHttp\Exception\ServerException $e) {

            throw new ServerErrorException(
                $e->getMessage(),
                $e->getCode()
            );

        } catch (\Exception $ex) {

            throw new ServerErrorException(
                $ex->getMessage(),
                $ex->getCode()
            );
        }
    }
}

if (!function_exists('build_req_resp_log')) {

    /**
     * 构建请求返回日志
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $response
     * @param array $queries
     * @param string $exception
     *
     * @return string
     */
    function build_req_resp_log(\Illuminate\Http\Request $request, $response, $queries = [], $exception = '')
    {
        $message = '';

        // 来源ip
        $srcIp = \AiLeZai\Common\Lib\Common\IpUtil::getCurrentIP();
        if (! empty($srcIp)) {
            $message .= sprintf("%s ", $srcIp);
        }

        // 接口
        $message .= sprintf("uri[%s] ", $request->path());

        // 执行时间
        $useTime = microtime(true) - LUMEN_START_TIME;
        $message .= sprintf("%.3F ", $useTime);

        // 请求参数
        if (! empty($request->all())) {
            if ($request->method() == 'GET') {
                $message .= sprintf("G%s ", json_encode($request->all()));
            } elseif ($request->method() == 'POST') {
                $message .= sprintf("P%s ", json_encode($request->all()));
            }
        }

        // cookie目前我们没有使用

        // php://input内容
        if (empty($_GET) && empty($_POST)) {
            $rawInput = file_get_contents('php://input');
            if (! empty($rawInput)) {
                $message .= sprintf("raw=%s ", $rawInput);
            }
        }

        // sql
        if (env('APP_DEBUG') == true) {
            $message .= sprintf("SQL%s ", json_encode($queries));
        }

        // response
        if ($response instanceof \Illuminate\Http\JsonResponse) {

            $data = $response->getData(true);
            if (env('APP_TYPE', 'api') === 'admin' && $request->expectsJson()) {  // ajax json返回

                $message .= sprintf("ajax_resp[%d,%s]", $data['code'], $data['msg']);

            } else { // api json 返回

                $errorCode = $data['errorCode'];
                $errorStr = ($data['errorCode'] == 0) ? '' : $data['errorStr'];
                $resultCount = $data['resultCount'];
                $message .= sprintf("resp[%d,%s,%d]", $errorCode, $errorStr, $resultCount);
            }

        } elseif (
            is_object($response) &&
            property_exists($response, 'original') &&
            is_object($response->original) &&
            $response->original instanceof \Illuminate\View\View
        ) {

            /**
             * @var \Illuminate\View\View $view
             */
            $view = $response->original;
            $message .= sprintf("view_resp[%s]", $view->getName());

        } elseif ($response instanceof \Illuminate\Http\RedirectResponse) {

            $message .= sprintf("redirect_resp[%s]", $response->getTargetUrl());

        } else {

            $message .= sprintf("else_resp[%s]", json_encode($response));
        }

        // exception
        if (!empty($exception) && $exception instanceof \Exception) {
            $message .= \AiLeZai\Common\Lib\Log\LOG::e2str($exception);
        }

        return $message;
    }
}

if (! function_exists('bcrypt')) {
    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    function bcrypt($value, $options = [])
    {
        return app('hash')->make($value, $options);
    }
}

if (! function_exists('check_vcode')) {

    /**
     * 校验验证码
     *
     * @param $captchaCode
     * @return bool
     */
    function check_vcode($captchaCode)
    {
        $vcode = session()->get('vcode');
        $vcode = $vcode ? explode('|', $vcode) : '';
        session()->remove('vcode');

        if (!$vcode || !isset($vcode[1]) || strtolower($vcode[0]) != strtolower($captchaCode) || $vcode[1] + 60 < time()) {
            return false;
        }

        return true;
    }
}