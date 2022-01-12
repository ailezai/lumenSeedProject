<?php

namespace System\Services\Log;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use System\Repositories\Log\LogLoginRepository;

class LogLoginService
{
    /**
     * @var LogLoginRepository
     */
    protected $logLoginRepository;

    /**
     * LogLoginService constructor.
     *
     * @param LogLoginRepository $logLoginRepository
     */
    public function __construct(LogLoginRepository $logLoginRepository)
    {
        $this->logLoginRepository = $logLoginRepository;
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
        return $this->logLoginRepository->listPaginateByFilter($filter, $size);
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
        // IP
        if (!empty($condition['ip'])) {
            $filter = sql_where()->field($filter, 'ip', ip2long($condition['ip']));
        }

        // 状态
        if (!empty($condition['status'])) {
            $filter = sql_where()->field($filter, 'status', $condition['status']);
        }

        return $filter;
    }
}