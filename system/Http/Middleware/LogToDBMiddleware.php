<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2018/2/11
 */

namespace System\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use AiLeZai\Common\Lib\Log\LOG;
use System\Enums\Log\LogOperationStatusEnum;
use System\Models\Log\LogSql;
use System\Services\Log\LogOperationService;

class LogToDBMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        DB::enableQueryLog();
        $response = $next($request);
        $queries = DB::getQueryLog();

        // 记录日志
        try {
            // 操作日志
            /**
             * @var LogOperationService $logOperationService
             */
            $logOperationService = app()->make('System\Services\Log\LogOperationService');
            $host = $request->getHost();
            $method = $request->method();
            $path = $request->path();
            $errorMessage = '';
            $status = LogOperationStatusEnum::getName(LogOperationStatusEnum::OPERATION_SUCCESS);
            $logOperationService->createLog($host, $method, $path, $request, $errorMessage, $status);

            // SQL日志
            if (!empty($queries) && is_array($queries)) {
                foreach ($queries as $query) {
                    $sqlLog[] = [
                        'trace_id' => LOG::getTraceId(),
                        'query' => $query['query'],
                        'bindings' => json_encode($query['bindings'], JSON_UNESCAPED_UNICODE),
                        'time' => $query['time'],
                        'create_time' => date('Y-m-d H:i:s'),
                        'modify_time' => date('Y-m-d H:i:s')
                    ];
                }
                LogSql::insert($sqlLog);
            }
        } catch (Exception $e) {
            LOG::error("打印日志异常：{$e->getMessage()}", null, null, 1);
        }

        return $response;
    }
}