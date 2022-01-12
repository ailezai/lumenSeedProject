<?php

namespace AiLeZai\Lumen\Framework\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use AiLeZai\Common\Lib\Log\LOG;

class LogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 请求开始，设置traceId
        if ($request->hasHeader('X-Trace-Id')) {
            LOG::setTraceId($request->header('X-Trace-Id'));
        }

        DB::enableQueryLog();
        $response = $next($request);
        $queries = DB::getQueryLog();

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            if (env('APP_DEBUG') == true) {
                $data['queries'] = $queries;
            }
            $data['timeCost']   = number_format(microtime(true) - LUMEN_START_TIME, 5, '.', '');
            $data['timeNow']    = date('Y-m-d H:i:s');
            $data['modelType']  = 'Response';
            $response->setData($data);
        }

        // 记录日志
        try {
            LOG::info(build_req_resp_log($request, $response, $queries));
        } catch (\Exception $exception) {
            LOG::error(sprintf('%s~%s %s:%d',
                    $exception->getCode(),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine())
            );
        }

        return $response;
    }
}