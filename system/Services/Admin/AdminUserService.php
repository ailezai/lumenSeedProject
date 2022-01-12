<?php
namespace System\Services\Admin;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use System\Enums\Admin\AdminUserStatusEnum;
use System\Exceptions\PermissionDenyException;
use System\Models\Admin\AdminUser;
use System\Repositories\Admin\AdminRoleRepository;
use System\Repositories\Admin\AdminUserRepository;
use System\Supports\HotRefresh;

class AdminUserService
{
    /**
     * @var AdminUserRepository
     */
    protected $adminUserRepository;

    /**
     * @var AdminRoleRepository
     */
    protected $adminRoleRepository;

    /**
     * AdminUserService constructor.
     * @param AdminUserRepository $adminUserRepository
     * @param AdminRoleRepository $adminRoleRepository
     */
    public function __construct(AdminUserRepository $adminUserRepository, AdminRoleRepository $adminRoleRepository)
    {
        $this->adminUserRepository = $adminUserRepository;
        $this->adminRoleRepository = $adminRoleRepository;
    }

    /**
     * 检查当前user_id是否可操作
     *
     * @param int $userId
     *
     * @throws PermissionDenyException
     */
    private function checkUserId(int $userId) {
        if ($userId == 1) {
            throw new PermissionDenyException('无法操作系统管理员');
        }

        // 获得可以管理的角色id
        $adminUserId = session()->get('admin_user_id');
        $adminUser = $this->adminUserRepository->getByAdminUserId($adminUserId);
        $roleIds = [];
        foreach ($adminUser->role_grant as $item) {
            if ($item->pivot->is_admin == 1) {
                $roleIds[] = $item->role_id;
                $roles = $this->adminRoleRepository->listAllByParentRoleId($item->role_id);
                if (count($roles) > 0) {
                    $roles = $roles->toArray();
                    $roleIds = array_merge($roleIds, array_column($roles, 'role_id'));
                }
            }
        }

        // 根据role_id获取所有的admin_user_id
        $roles = $this->adminRoleRepository->listByRoleIds($roleIds);
        $adminUserIds = [];
        foreach ($roles as $role) {
            $adminUsers = $role->users->toArray();
            if (!empty($adminUsers)) {
                $adminUserIds = array_merge($adminUserIds, array_column($adminUsers, 'admin_user_id'));
            }
        }
        $adminUserIds = array_unique($adminUserIds);

        if (!in_array($userId, $adminUserIds)) {
            throw new PermissionDenyException('无法操作该角色');
        }
    }

    /**
     * 获取管理员详细信息
     *
     * @param int $adminUserId
     *
     * @return null|AdminUser
     *
     * @throws PermissionDenyException
     */
    public function getByAdminUserId(int $adminUserId)
    {
        if (session()->get('admin_user_id') != 1) {
            $this->checkUserId($adminUserId);
        }

        return $this->adminUserRepository->getByAdminUserId($adminUserId);
    }

    /**
     * 获取所有管理员id和姓名
     *
     * @return Collection
     */
    public function listAllAdminUserName()
    {
        return $this->adminUserRepository->listAllAdminUserName();
    }

    /**
     * 获取所有管理员id和姓名
     *
     * @return Collection
     */
    public function listAllAdminUserNameAndMail()
    {
        return $this->adminUserRepository->listAllAdminUserNameAndMail();
    }

    /**
     * 分页展示所有管理员
     *
     * @param array $condition
     *
     * @return LengthAwarePaginator
     */
    public function listPaginateByCondition(array $condition = [])
    {
        $filter = $this->setFilter($condition);
        $size = config('webConfig.paginate.x-large');
        return $this->adminUserRepository->listPaginateByFilter($filter, $size);
    }

    /**
     * 展示所有可管理的管理员
     *
     * @param array $condition
     *
     * @return LengthAwarePaginator
     */
    public function listPaginateByConditionInManage(array $condition = [])
    {
        $filter = $this->setFilter($condition);
        $size = config('webConfig.paginate.x-large');

        // 获得可以管理的角色id
        $adminUserId = session()->get('admin_user_id');
        $adminUser = $this->adminUserRepository->getByAdminUserId($adminUserId);
        $roleIds = [];
        foreach ($adminUser->role_grant as $item) {
            if ($item->pivot->is_admin == 1) {
                $roleIds[] = $item->role_id;
                $roles = $this->adminRoleRepository->listAllByParentRoleId($item->role_id);
                if (count($roles) > 0) {
                    $roles = $roles->toArray();
                    $roleIds = array_merge($roleIds, array_column($roles, 'role_id'));
                }
            }
        }

        // 根据role_id获取所有的admin_user_id
        $roles = $this->adminRoleRepository->listByRoleIds($roleIds);
        $adminUserIds = [];
        foreach ($roles as $role) {
            $adminUsers = $role->users->toArray();
            if (!empty($adminUsers)) {
                $adminUserIds = array_merge($adminUserIds, array_column($adminUsers, 'admin_user_id'));
            }
        }
        $adminUserIds = array_unique($adminUserIds);

        $filter = sql_where()->inField($filter, 'admin_user_id', $adminUserIds);

        return $this->adminUserRepository->listPaginateByFilter($filter, $size);
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
        if (!empty($condition['username'])) {
            $filter = sql_where()->field($filter, 'username', $condition['username']);
        }

        if (!empty($condition['name'])) {
            $filter = sql_where()->field($filter, 'name', "%{$condition['name']}%", 'like');
        }

        return $filter;
    }

    /**
     * 更新用户状态
     *
     * @param int $adminUserId
     * @param string $status
     *
     * @throws PermissionDenyException
     */
    public function updateStatusByUserId(int $adminUserId, string $status)
    {
        $this->checkUserId($adminUserId);

        if ($adminUserId == session()->get('admin_user_id')) {
            throw new PermissionDenyException('无法修改个人状态');
        }

        $data = [
            'status' => $status
        ];
        $this->adminUserRepository->updateByAdminUserId($adminUserId, $data);
        HotRefresh::refreshByUserId($adminUserId);
    }

    /**
     * 新增管理员
     *
     * @param array $params
     *
     * @throws Exception
     * @throws PermissionDenyException
     */
    public function addSubmit(array $params) {
        $params = $this->checkSubmitParams($params);

        // 校验登录名
        $ExistAdminUser = $this->adminUserRepository->getByUsername($params['username']);
        if (!empty($ExistAdminUser)) {
            throw new PermissionDenyException('登录名已存在，请修改');
        }

        // 校验密码
        if (empty($params['password'])) {
            throw new DataNotFoundException('密码不能为空');
        }
        if (strlen($params['password']) < 6) {
            throw new DataNotFoundException('密码不少于6位');
        }

        $data = [
            'username' => $params['username'],
            'password' => password_hash($params['password'], PASSWORD_BCRYPT),
            'name' => $params['name'],
            'mail' => $params['mail'],
            'mobile' => $params['mobile'],
            'status' => AdminUserStatusEnum::getName(AdminUserStatusEnum::NORMAL),
        ];

        DB::beginTransaction();
        try {
            $adminUser = $this->adminUserRepository->create($data);
            $adminUser->role_grant()->sync($params['role']);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        };
    }

    /**
     * 编辑管理员
     *
     * @param $userId
     * @param array $params
     *
     * @throws Exception
     * @throws PermissionDenyException
     */
    public function editSubmit($userId, array $params) {
        if (!empty($userId)) {
            $this->checkUserId($userId);
        }
        $params = $this->checkSubmitParams($params);

        DB::beginTransaction();
        try {
            $data = [
                'name' => $params['name'],
                'mail' => $params['mail'],
                'mobile' => $params['mobile'],
            ];
            $adminUser = $this->adminUserRepository->getByAdminUserId($userId);
            $this->adminUserRepository->updateByAdminUserId($userId, $data);

            // 角色权限修改，保留原来的权限
            $roleIds = session()->get('admin_user_role_ids');
            $adminUserId = session()->get('admin_user_id');
            $roleList = $this->adminRoleRepository->listAllByRoleIdsAndAdminUserId($roleIds, $adminUserId);
            $roles = [];
            foreach ($roleList as $item) {
                $roles[] = $item->role_id;
                $roles = array_merge($roles, array_column($item->children_role->toArray() ?? [], 'role_id'));
            }
            foreach ($adminUser->role_grant as $item) {
                if ($item->pivot->is_admin == 1) {
                    unset($roles[$item->role_id]);
                    unset($params['role'][$item->role_id]);
                }
            }
            $adminUser->role_grant()->detach($roles);
            $adminUser->role_grant()->syncWithoutDetaching($params['role']);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        };
    }

    /**
     * 校验提交参数
     *
     * @param array $params
     *
     * @return array
     */
    private function checkSubmitParams(array $params)
    {
        if (empty($params['mail'])) {
            $params['mail'] = null;
        }
        if (empty($params['mobile'])) {
            $params['mobile'] = null;
        }

        // 可设置的所有角色
        $roleIds = session()->get('admin_user_role_ids');
        $adminUserId = session()->get('admin_user_id');
        $roleList = $this->adminRoleRepository->listAllByRoleIdsAndAdminUserId($roleIds, $adminUserId);
        $roles = [];
        foreach ($roleList as &$item) {
            $item->users = $item->users->toArray()[0];
            $roles[$item->role_id] = 'NORMAL';
            if ($item->users['pivot']['is_admin'] == 1) {
                foreach ($item->children_role as $subItem) {
                    $roles[$subItem->role_id] = 'ADMIN';
                }
            }
        }

        // 处理角色参数
        $role = [];
        if (empty($params['role'])) {
            $params['role'] = [];
        } else {
            // 过滤角色管理员
            if (empty($params['role_admin'])) {
                $params['role_admin'] = [];
            }

            // 过滤角色
            foreach ($params['role'] as $param) {
                if (!empty($roles[$param])) {
                    if ($roles[$param] == 'ADMIN' && in_array($param, $params['role_admin'])) {
                        $role[$param] = [
                            'is_admin' => 1,
                        ];
                    } else {
                        $role[$param] = [
                            'is_admin' => 0,
                        ];
                    }
                }
            }
        }

        // 处理多余参数
        unset($params['role_admin']);
        $params['role'] = $role;
        return $params;
    }

    /**
     * 重置密码
     *
     * @param int $adminUserId
     * @param string $password
     *
     * @throws PermissionDenyException
     */
    public function resetPassword(int $adminUserId, string $password)
    {
        if (session()->get('admin_user_id') != 1) {
            $this->checkUserId($adminUserId);
        }

        if ($adminUserId == session()->get('admin_user_id')) {
            throw new PermissionDenyException('请前往个人信息页面修改密码');
        }

        $password = password_hash($password, PASSWORD_BCRYPT);
        $this->adminUserRepository->updateByAdminUserId($adminUserId, ['password' => $password]);
    }

    /**
     * 独立授权提交
     *
     * @param int $adminUserId
     * @param array $grant
     * @param array $forbid
     *
     * @throws PermissionDenyException
     * @throws DataNotFoundException
     */
    public function permissionSubmit(int $adminUserId, array $grant, array $forbid)
    {
        if (session()->get('admin_user_id') != 1) {
            throw new PermissionDenyException('权限不足');
        }
        if ($adminUserId == 1) {
            throw new PermissionDenyException('无法操作系统管理员');
        }

        $adminUser = $this->adminUserRepository->getByAdminUserId($adminUserId);
        if (empty($adminUser)) {
            throw new DataNotFoundException('管理员不存在');
        }

        $adminUser->permission_grant()->sync($grant);
        $adminUser->permission_forbid()->sync($forbid);
    }
}