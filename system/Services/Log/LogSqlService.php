<?php
namespace System\Services\Log;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use System\Repositories\Log\LogSqlRepository;

class LogSqlService
{
    /**
     * @var LogSqlRepository
     */
    protected $logSqlRepository;

    /**
     * LogSqlService constructor.
     *
     * @param LogSqlRepository $logSqlRepository
     */
    public function __construct(LogSqlRepository $logSqlRepository)
    {
        $this->logSqlRepository = $logSqlRepository;
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
        return $this->logSqlRepository->listPaginateByFilter($filter, $size);
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

        // Sql 执行时间筛选
        $firstValue = $condition['time1'];
        $secondValue = $condition['time2'];
        $filter = sql_where()->betweenField($filter, 'time', $firstValue, $secondValue);
        return $filter;
    }
}