<?php
namespace System\Repositories\Log;

use Exception;
use System\Models\Log\LogSql;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class LogSqlRepository
{
    /**
     * @var LogSql
     */
    protected $logSql;

    /**
     * LogSqlRepository constructor.
     *
     * @param LogSql $logSql
     */
    public function __construct(LogSql $logSql)
    {
        $this->logSql = $logSql;
    }

    /**
     * 返回空对象
     *
     * @return LogSql
     */
    public function getEmptyObject()
    {
        return $this->logSql;
    }

    /**
     * 根据主键id查找
     *
     * @param $id
     *
     * @return LogSql|null
     */
    public function getById($id)
    {
        return $this->logSql
            ->selectFullFields()
            ->where('id', $id)
            ->first();
    }

    /**
     * 根据filter过滤，分页查询
     *
     * @param array   $filter 过滤条件
     * @param integer $size   分页大小
     *
     * @return LengthAwarePaginator
     */
    public function listPaginateByFilter($filter = [], $size = 20)
    {
        return $this->logSql
            ->selectFullFields()
            ->whereByFilter($filter)
            ->orderBy('id', 'DESC')
            ->paginate($size);
    }

    /**
     * 创建数据
     *
     * @param array $data 新增字段
     *
     * @return LogSql
     */
    public function create(array $data)
    {
        return $this->logSql->create($data);
    }

    /**
     * 创建数据或返回已存在数据
     *
     * @param array $attributes 比较字段
     * @param array $joining    更新字段
     *
     * @return Model
     */
    public function firstOrCreate(array $attributes, array $joining)
    {
        return $this->logSql->firstOrCreate($attributes, $joining);
    }

    /**
     * 根据主键id更新
     *
     * @param $id
     * @param array $data        更新字段
     *
     * @return int
     */
    public function updateById($id, array $data)
    {
        return $this->logSql
            ->where('id', $id)
            ->update($data);
    }

    /**
     * 根据主键id更新或创建数据
     *
     * @param array $attributes    比较字段
     * @param array $values        更新字段
     *
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->logSql->updateOrCreate($attributes, $values);
    }

    /**
     * 根据主键id删除
     *
     * @param $id
     *
     * @return int
     *
     * @throws Exception
     */
    public function deleteById($id)
    {
        return $this->logSql
            ->where('id', $id)
            ->delete();
    }
}