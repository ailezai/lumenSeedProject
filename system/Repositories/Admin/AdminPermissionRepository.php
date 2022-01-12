<?php
namespace System\Repositories\Admin;

use Exception;
use Illuminate\Support\Facades\DB;
use System\Models\Admin\AdminPermission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AdminPermissionRepository
{
    /**
     * @var AdminPermission
     */
    protected $adminPermission;

    /**
     * AdminPermissionRepository constructor.
     *
     * @param AdminPermission $adminPermission
     */
    public function __construct(AdminPermission $adminPermission)
    {
        $this->adminPermission = $adminPermission;
    }

    /**
     * 返回空对象
     *
     * @return AdminPermission
     */
    public function getEmptyObject()
    {
        return $this->adminPermission;
    }

    /**
     * 根据主键permission_id查找
     *
     * @param $permissionId
     *
     * @return AdminPermission|null
     */
    public function getByPermissionId($permissionId)
    {
        return $this->adminPermission
            ->selectFullFields()
            ->where('permission_id', $permissionId)
            ->first();
    }

    /**
     * 根据alias查找
     *
     * @param string $alias
     *
     * @return AdminPermission|null|static
     */
    public function getByAlias(string $alias)
    {
        return $this->adminPermission
            ->selectFullFields()
            ->where('alias', $alias)
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
        return $this->adminPermission
            ->selectFullFields()
            ->whereByFilter($filter)
            ->paginate($size);
    }

    /**
     * 获取所有权限
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function listAll()
    {
        return $this->adminPermission->selectFullFields()->get();
    }

    /**
     * 根据permission_id获取权限
     *
     * @param array $permissionIds
     *
     * @return \Illuminate\Support\Collection
     */
    public function listAllByPermissionIds(array $permissionIds)
    {
        return $this->adminPermission
            ->selectFullFields()
            ->whereIn('permission_id', $permissionIds)
            ->get();
    }

    /**
     * 创建数据
     *
     * @param array $data 新增字段
     *
     * @return AdminPermission
     */
    public function create(array $data)
    {
        return $this->adminPermission->create($data);
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
        return $this->adminPermission->firstOrCreate($attributes, $joining);
    }

    /**
     * 根据主键permission_id更新
     *
     * @param $permissionId
     * @param array $data        更新字段
     *
     * @return int
     */
    public function updateByPermissionId($permissionId, array $data)
    {
        return $this->adminPermission
            ->where('permission_id', $permissionId)
            ->update($data);
    }

    /**
     * 根据主键permission_id更新或创建数据
     *
     * @param array $attributes    比较字段
     * @param array $values        更新字段
     *
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->adminPermission->updateOrCreate($attributes, $values);
    }

    /**
     * 根据主键permission_id删除
     *
     * @param $permissionId
     *
     * @throws Exception
     */
    public function deleteByPermissionId($permissionId)
    {
        DB::beginTransaction();
        try {
            $adminPermission = $this->getByPermissionId($permissionId);
            $adminPermission->roles()->detach();
            $adminPermission->user_grant()->detach();
            $adminPermission->user_forbid()->detach();
            $adminPermission->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}