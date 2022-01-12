<?php
namespace System\Services\Admin;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use System\Exceptions\PermissionDenyException;
use System\Models\Admin\AdminPermission;
use System\Repositories\Admin\AdminPermissionRepository;
use System\Supports\HotRefresh;

class AdminPermissionService
{
    /**
     * @var AdminPermissionRepository
     */
    protected $adminPermissionRepository;

    /**
     * AdminPermissionService constructor.
     *
     * @param AdminPermissionRepository $adminPermissionRepository
     */
    public function __construct(AdminPermissionRepository $adminPermissionRepository)
    {
        $this->adminPermissionRepository = $adminPermissionRepository;
    }

    /**
     * 根据permission_id获取权限详情
     *
     * @param $permissionId
     *
     * @return null|AdminPermission
     */
    public function getByPermissionId($permissionId)
    {
        return $this->adminPermissionRepository->getByPermissionId($permissionId);
    }

    /**
     * 分页展示权限
     *
     * @param array $condition
     *
     * @return LengthAwarePaginator
     */
    public function listPaginateByCondition(array $condition = [])
    {
        $filter = $this->setFilter($condition);
        $size = config('webConfig.paginate.large');
        return $this->adminPermissionRepository->listPaginateByFilter($filter, $size);
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
        $filter = sql_where()->field($filter, 'permission_id', '6', '>');
        return $filter;
    }

    /**
     * 新增权限
     *
     * @param array $params
     *
     * @throws PermissionDenyException
     */
    public function addSubmit(array $params)
    {
        if (session()->get('admin_user_id') != 1
            && (stripos($params['path'], 'system') !== false
            || stripos($params['path'], 'log') !== false
            || stripos($params['path'], '*') !== false)
        ) {
            throw new PermissionDenyException('存在敏感权限，请联系系统管理员设置');
        }

        $permission = $this->adminPermissionRepository->getByAlias($params['alias']);

        $params['path'] = str_replace("\r\n", ',', $params['path']);
        if (!empty($permission)) {
            throw new PermissionDenyException('权限标识符已存在，请修改');
        }
        $permission = $this->adminPermissionRepository->create($params);
        $permission->roles()->attach(1);
    }

    /**
     * 修改权限
     *
     * @param $permissionId
     * @param array $params
     *
     * @throws PermissionDenyException
     */
    public function editSubmit($permissionId, array $params)
    {
        if (session()->get('admin_user_id') != 1
            && (stripos($params['path'], 'system') !== false
                || stripos($params['path'], 'log') !== false
                || stripos($params['path'], '*') !== false)
        ) {
            throw new PermissionDenyException('存在敏感权限，请联系系统管理员设置');
        }

        $permission = $this->adminPermissionRepository->getByAlias($params['alias']);

        $params['path'] = str_replace("\r\n", ',', $params['path']);

        if (!empty($permission) && $permission->permission_id != $permissionId) {
            throw new PermissionDenyException('权限标识符已存在，请修改');
        }
        $this->adminPermissionRepository->updateByPermissionId($permissionId, $params);

        HotRefresh::refreshByPermissionId($permissionId);
    }

    /**
     * 删除权限
     *
     * @param int $permissionId
     *
     * @throws PermissionDenyException
     * @throws Exception
     */
    public function delete(int $permissionId)
    {
        if (session()->get('admin_user_id') != 1) {
            throw new PermissionDenyException('仅限系统管理员删除');
        }
        if ($permissionId == 1) {
            throw new PermissionDenyException('该权限受保护，无法删除');
        }

        $adminUserIds = HotRefresh::refreshByPermissionId($permissionId, false);
        $this->adminPermissionRepository->deleteByPermissionId($permissionId);
        HotRefresh::refreshByUserIds($adminUserIds);
    }

    /**
     * 获取所有权限
     *
     * @return Collection|static[]
     */
    public function listAllPermission()
    {
        return $this->adminPermissionRepository->listAll();
    }
}