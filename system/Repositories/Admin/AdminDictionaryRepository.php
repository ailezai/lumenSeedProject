<?php
namespace System\Repositories\Admin;

use Exception;
use System\Models\Admin\AdminDictionary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AdminDictionaryRepository
{
    /**
     * @var AdminDictionary
     */
    protected $adminDictionary;

    /**
     * AdminDictionaryRepository constructor.
     *
     * @param AdminDictionary $adminDictionary
     */
    public function __construct(AdminDictionary $adminDictionary)
    {
        $this->adminDictionary = $adminDictionary;
    }

    /**
     * 返回空对象
     *
     * @return AdminDictionary
     */
    public function getEmptyObject()
    {
        return $this->adminDictionary;
    }

    /**
     * 根据主键id查找
     *
     * @param $id
     *
     * @return AdminDictionary|null
     */
    public function getById($id)
    {
        return $this->adminDictionary
            ->selectFullFields()
            ->where('id', $id)
            ->first();
    }

    /**
     * 根据name查找
     *
     * @param string $name
     *
     * @return AdminDictionary|null
     */
    public function getByName(string $name)
    {
        return $this->adminDictionary
            ->selectFullFields()
            ->where('name', $name)
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
        return $this->adminDictionary
            ->selectFullFields()
            ->whereByFilter($filter)
            ->paginate($size);
    }

    /**
     * 创建数据
     *
     * @param array $data 新增字段
     *
     * @return AdminDictionary
     */
    public function create(array $data)
    {
        return $this->adminDictionary->create($data);
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
        return $this->adminDictionary->firstOrCreate($attributes, $joining);
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
        return $this->adminDictionary
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
        return $this->adminDictionary->updateOrCreate($attributes, $values);
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
        return $this->adminDictionary
            ->where('id', $id)
            ->delete();
    }
}