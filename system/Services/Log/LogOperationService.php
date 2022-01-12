<?php

namespace System\Services\Log;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use AiLeZai\Common\Lib\Common\IpUtil;
use AiLeZai\Common\Lib\Log\LOG;
use Exception;
use System\Repositories\Log\LogOperationRepository;

class LogOperationService
{
    /**
     * @var LogOperationRepository
     */
    protected $logOperationRepository;

    /**
     * LogOperationService constructor.
     *
     * @param LogOperationRepository $logOperationRepository
     */
    public function __construct(LogOperationRepository $logOperationRepository)
    {
        $this->logOperationRepository = $logOperationRepository;
    }

    /**
     * 分页展示日志
     *
     * @param array $condition
     *
     * @return LengthAwarePaginator
     */
    public function listPaginateByCondition(array $condition)
    {
        $filter = $this->setFilter($condition);
        $size = config('webConfig.paginate.xx-large');
        return $this->logOperationRepository->listPaginateByFilter($filter, $size);
    }

    /**
     * 设置过滤条件
     *
     * @param array $condition  筛选条件
     * @param array $filter     过滤数组
     *
     * @return array
     */
    private function setFilter(array $condition, array $filter = [])
    {
        // trace_id 筛选
        if (!empty($condition['trace_id'])) {
            $filter = sql_where()->field($filter, 'trace_id', $condition['trace_id']);
        }

        // 管理员
        if (!empty($condition['admin_user_id']) || $condition['admin_user_id'] === "0") {
            $filter = sql_where()->field($filter, 'admin_user_id', $condition['admin_user_id']);
        }

        // IP
        if (!empty($condition['ip'])) {
            $filter = sql_where()->field($filter, 'ip', ip2long($condition['ip']));
        }

        // 路径
        if (!empty($condition['path'])) {
            $filter = sql_where()->field($filter, 'path', $condition['path']);
        }

        // 状态
        if (!empty($condition['status'])) {
            $filter = sql_where()->field($filter, 'status', $condition['status']);
        }

        return $filter;
    }

    /**
     * @param string $host          域名/主机
     * @param string $method        请求方法
     * @param string $path          路由
     * @param Request $request      请求内容
     * @param string $errorMessage  错误信息
     * @param string $status        请求结果
     */
    public function createLog(string $host, string $method, string $path, Request $request, string $errorMessage, string $status)
    {
        try {
            // 过滤路由
            $urls = [
                'GET-login',
                'GET-vCode',
                'GET-forget',
                'GET-reset',
            ];
            if (in_array($method.'-'.$path, $urls)) {
                return;
            }

            // 过滤参数
            $request = $request->toArray();
            unset($request['password']);
            unset($request['password_confirmation']);

            $request = json_encode($request, JSON_UNESCAPED_UNICODE);
            $data = [
                'trace_id' => LOG::getTraceId(),
                'admin_user_id' => session()->get('admin_user_id', 0),
                'name' => session()->get('admin_user_name', '*未知管理员'),
                'ip' => ip2long(IpUtil::getCurrentIP()),
                'session' => session()->getId(),
                'host' => $host,
                'method' => $method,
                'path' => $path,
                'request' => $request,
                'error_message' => $errorMessage,
                'status' => $status,
            ];
            $this->logOperationRepository->create($data);
        } catch (Exception $e) {

        }
    }
}