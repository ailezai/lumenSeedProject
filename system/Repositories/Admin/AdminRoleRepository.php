<?php
namespace System\Repositories\Admin;

use Exception;
use Illuminate\Support\Facades\DB;
use System\Models\Admin\AdminRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AdminRoleRepository
{
    /**
     * @var AdminRole
     */
    protected $adminRole;

    /**
     * AdminRoleRepository constructor.
     *
     * @param AdminRole $adminRole
     */
    public function __construct(AdminRole $adminRole)
    {
        $this->adminRole = $adminRole;
    }

    /**
     * 返回空对象
     *
     * @return AdminRole
     */
    public function getEmptyObject()
    {
        return $this->adminRole;
    }

    /**
     * 根据主键role_id查找
     *
     * @param $roleId
     *
     * @return AdminRole|null
     */
    public function getByRoleId($roleId)
    {
        return $this->adminRole
            ->selectFullFields()
            ->with('permission_grant')
            ->where('role_id', $roleId)
            ->first();
    }

    /**
     * 根据alias查找
     *
     * @param string $alias
     *
     * @return AdminRole
     */
    public function getByAlias(string $alias)
    {
        return $this->adminRole
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
        return $this->adminRole
            ->selectFullFields()
            ->with('parent_role')
            ->whereByFilter($filter)
            ->paginate($size);
    }

    /**
     * 根据role_ids获取角色信息
     *
     * @param int $roleId
     * @param $adminUserId
     *
     * @return AdminRole
     */
    public function getByRoleIdAndAdminUserId(int $roleId, $adminUserId)
    {
        return $this->adminRole
            ->selectFullFields()
            ->with([ 'users'=> function ($query) use($adminUserId) {
                return $query->wherePivot('admin_user_id', $adminUserId);
            }])
            ->where('role_id', $roleId)
            ->first();
    }

    /**
     * 根据role_ids获取角色及子角色信息
     *
     * @param array   $roleIds
     * @param integer $adminUserId
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function listAllByRoleIdsAndAdminUserId(array $roleIds, int $adminUserId)
    {
        return $this->adminRole
            ->selectFullFields()
            ->with([ 'users'=> function ($query) use($adminUserId) {
                return $query->wherePivot('admin_user_id', $adminUserId);
            }])
            ->with('children_role')
            ->whereIn('role_id', $roleIds)
            ->get();
    }

    /**
     * 根据parent_role_id获取角色
     *
     * @param int $parentRoleId
     *
     * @return \Illuminate\Support\Collection
     */
    public function listAllByParentRoleId(int $parentRoleId)
    {
        return $this->adminRole
            ->selectFullFields()
            ->where('parent_role_id', $parentRoleId)
            ->get();
    }

    /**
     * 创建数据
     *
     * @param array $data 新增字段
     *
     * @return AdminRole
     */
    public function create(array $data)
    {
        return $this->adminRole->create($data);
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
        return $this->adminRole->firstOrCreate($attributes, $joining);
    }

    /**
     * 根据主键role_id更新
     *
     * @param $roleId
     * @param array $data        更新字段
     *
     * @return int
     */
    public function updateByRoleId($roleId, array $data)
    {
        return $this->adminRole
            ->where('role_id', $roleId)
            ->update($data);
    }

    /**
     * 根据主键role_id更新或创建数据
     *
     * @param array $attributes    比较字段
     * @param array $values        更新字段
     *
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->adminRole->updateOrCreate($attributes, $values);
    }

    /**
     * 根据主键role_id删除
     *
     * @param $roleId
     *
     * @throws \Exception
     */
    public function deleteByRoleId($roleId)
    {
        DB::beginTransaction();
        try {
            $adminPermission = $this->getByRoleId($roleId);
            $adminPermission->users()->detach();
            $adminPermission->permission_grant()->detach();
            $adminPermission->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * 根据角色id获取相关信息
     *
     * @param array $roleIds
     *
     * @return \Illuminate\Support\Collection
     */
    public function listByRoleIds(array $roleIds)
    {
        return $this->adminRole
            ->selectFullFields()
            ->withUsers()
            ->withPermissionGrant()
            ->whereIn('role_id', $roleIds)
            ->get();
    }
}