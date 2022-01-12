<?php
namespace System\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use Exception;
use System\Exceptions\PermissionDenyException;
use System\Models\Admin\AdminRole;
use System\Repositories\Admin\AdminPermissionRepository;
use System\Repositories\Admin\AdminRoleRepository;
use System\Supports\HotRefresh;

class AdminRoleService
{
    /**
     * @var AdminRoleRepository
     */
    protected $adminRoleRepository;

    /**
     * @var AdminPermissionRepository
     */
    protected $adminPermissionRepository;

    /**
     * AdminRoleService constructor.
     * @param AdminRoleRepository $adminRoleRepository
     * @param AdminPermissionRepository $adminPermissionRepository
     */
    public function __construct(AdminRoleRepository $adminRoleRepository, AdminPermissionRepository $adminPermissionRepository)
    {
        $this->adminRoleRepository = $adminRoleRepository;
        $this->adminPermissionRepository = $adminPermissionRepository;
    }

    /**
     * 检查当前role_id是否可操作
     *
     * @param integer $roleId        当前角色id
     * @param integer $parentRoleId  父角色id
     *
     * @throws PermissionDenyException
     */
    private function checkRoleId(int $roleId, int $parentRoleId = -1)
    {
        if ($roleId == 1) {
            throw new PermissionDenyException('该角色受保护，无法编辑');
        }

        if (session()->get('admin_user_id') == 1) {
            return;
        }

        if (!empty($roleId)) {
            $role = $this->adminRoleRepository->getByRoleId($roleId);
            $parentRoleId = $role->parent_role_id;
        }

        $adminUserId = session()->get('admin_user_id');
        $parentRole = $this->adminRoleRepository->getByRoleIdAndAdminUserId($parentRoleId, $adminUserId);
        $isAdmin = $parentRole->users[0]['pivot']['is_admin'] ?? 0;
        if ($isAdmin != 1) {
            throw new PermissionDenyException('权限不足，无法操作');
        }
    }

    /**
     * 根据role_id获取权限详情
     *
     * @param $roleId
     *
     * @return null|AdminRole
     */
    public function getByRoleId($roleId)
    {
        return $this->adminRoleRepository->getByRoleId($roleId);
    }

    /**
     * 分页展示所有角色
     *
     * @param array $condition
     *
     * @return LengthAwarePaginator
     */
    public function listPaginateByCondition(array $condition = [])
    {
        $filter = $this->setFilter($condition);
        $size = config('webConfig.paginate.large');
        return $this->adminRoleRepository->listPaginateByFilter($filter, $size);
    }

    /**
     * 展示所有可管理角色
     *
     * @param array $condition
     *
     * @return Collection|static[]
     */
    public function listAllByCondition(array $condition = [])
    {
        $roleIds = session()->get('admin_user_role_ids');
        $adminUserId = session()->get('admin_user_id');
        $list = $this->adminRoleRepository->listAllByRoleIdsAndAdminUserId($roleIds, $adminUserId);
        foreach ($list as &$item) {
            $item->users = $item->users->toArray()[0];
        }
        return $list;
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
        return $filter;
    }

    /**
     * 新建角色
     *
     * @param array $params
     *
     * @throws DataNotFoundException
     * @throws PermissionDenyException
     */
    public function addSubmit(array $params)
    {
        // 获取上级角色
        $parentRoleId = $params['parentRoleId'];
        $adminUserId = session()->get('admin_user_id');
        $parentRole = $this->adminRoleRepository->getByRoleIdAndAdminUserId($parentRoleId, $adminUserId);
        if (empty($parentRole)) {
            throw new DataNotFoundException('上级角色不存在');
        }

        // 检查alias是否存在
        $ExistsRole = $this->adminRoleRepository->getByAlias($params['alias']);
        if (!empty($ExistsRole)) {
            throw new PermissionDenyException('角色标识符已存在，请修改');
        }

        // 校验和设置权限
        if ($params['permission'] == [1]) {
            throw new PermissionDenyException('无法设置该权限');
        }
        $params['permission'] = array_diff($params['permission'], [1]);
        $parentRolePermission = array_column($parentRole->permission_grant->toArray(), 'permission_id');
        $params['permission'] = array_intersect($parentRolePermission, $params['permission']);

        $data = [
            'parent_role_id' => $params['parentRoleId'],
            'parent_role_list' => $parentRole->parent_role_list.','.$parentRole->role_id,
            'alias' => $params['alias'],
            'name' => $params['name'],
            'type' => $params['type'],
            'group' => $params['group'],
        ];

        DB::beginTransaction();
        try {
            $role = $this->adminRoleRepository->create($data);
            $role->permission_grant()->sync($params['permission']);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        };
    }

    /**
     * 修改角色
     *
     * @param $roleId
     * @param array $params
     *
     * @throws DataNotFoundException
     * @throws PermissionDenyException
     */
    public function editSubmit($roleId, array $params)
    {
        $this->checkRoleId($roleId, $params['parentRoleId']);

        $role = $this->adminRoleRepository->getByRoleId($roleId);
        $parentRoleId = $role->parent_role_id;
        $adminUserId = session()->get('admin_user_id');
        $parentRole = $this->adminRoleRepository->getByRoleIdAndAdminUserId($parentRoleId, $adminUserId);
        if (empty($parentRole)) {
            throw new DataNotFoundException('上级角色不存在');
        }

        // 检查alias是否存在
        $ExistsRole = $this->adminRoleRepository->getByAlias($params['alias']);
        if (!empty($ExistsRole) && $ExistsRole->role_id != $roleId) {
            throw new PermissionDenyException('角色标识符已存在，请修改');
        }

        // 校验和设置权限
        if ($params['permission'] == [1]) {
            throw new PermissionDenyException('无法设置该权限');
        }
        $params['permission'] = array_diff($params['permission'], [1]);
        $parentRolePermission = array_column($parentRole->permission_grant->toArray(), 'permission_id');
        $params['permission'] = array_intersect($parentRolePermission, $params['permission']);

        // 获取被删除的permission_id
        $role = $this->adminRoleRepository->getByRoleId($roleId);
        $permissionIds = $role->permission_grant->toArray();
        $permissionIds = array_column($permissionIds, 'permission_id');
        $deletePermissionIds = array_diff($permissionIds, $params['permission']);

        $data = [
            'alias' => $params['alias'],
            'name' => $params['name'],
            'type' => $params['type'],
            'group' => $params['group'],
        ];
        $this->adminRoleRepository->updateByRoleId($roleId, $data);
        $role->permission_grant()->sync($params['permission']);

        DB::beginTransaction();
        try {
            $this->updatePermission($role, $deletePermissionIds);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * 更新角色及其子角色分组
     *
     * @param AdminRole $role
     *
     * @param array $deletePermissionIds
     */
    private function updatePermission(AdminRole $role, array $deletePermissionIds)
    {
        $roleId = $role->role_id;
        $row = $role->permission_grant()->detach($deletePermissionIds);
        HotRefresh::refreshByRoleId($roleId);
        if ($row > 0) {
            $roles = $this->adminRoleRepository->listAllByParentRoleId($roleId);
            if (count($roles) > 0) {
                foreach ($roles as $item) {
                    $this->updatePermission($item, $deletePermissionIds);
                }
            }
        }
    }

    /**
     * 删除角色
     *
     * @param int $roleId
     *
     * @throws PermissionDenyException
     * @throws Exception
     */
    public function delete(int $roleId)
    {
        $this->checkRoleId($roleId);

        $this->deleteSubRole($roleId);
    }

    /**
     * 递归删除当前角色和子角色
     *
     * @param int $roleId
     *
     * @throws Exception
     */
    private function deleteSubRole(int $roleId)
    {
        $adminUserIds = HotRefresh::refreshByRoleId($roleId, false);
        $this->adminRoleRepository->deleteByRoleId($roleId);
        HotRefresh::refreshByUserIds($adminUserIds);

        $roles = $this->adminRoleRepository->listAllByParentRoleId($roleId);
        if (count($roles) > 0) {
            foreach ($roles as $item) {
                $this->deleteSubRole($item->role_id);
            }
        }
    }
}