<?php
namespace System\Services;

use Exception;
use AiLeZai\Common\Lib\Common\IpUtil;
use AiLeZai\Common\Lib\Jwt\JwtUtil;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use ReflectionException;
use System\Enums\Admin\AdminUserStatusEnum;
use System\Enums\Log\LogLoginStatusEnum;
use System\Exceptions\LoginException;
use System\Exceptions\PermissionDenyException;
use System\Models\Admin\AdminUser;
use System\Repositories\Admin\AdminMenuRepository;
use System\Repositories\Admin\AdminPermissionRepository;
use System\Repositories\Admin\AdminRoleRepository;
use System\Repositories\Admin\AdminUserRepository;
use System\Repositories\Log\LogLoginRepository;
use System\Supports\JwtHelper;
use System\Supports\Permission;
use System\Supports\TwoFactorAuthenticationUtil;

class LoginService extends BaseService
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
     * @var AdminPermissionRepository
     */
    protected $adminPermissionRepository;

    /**
     * @var AdminMenuRepository
     */
    protected $adminMenuRepository;

    /**
     * @var LogLoginRepository
     */
    protected $logLoginRepository;

    /**
     * LoginService constructor.
     * @param AdminUserRepository $adminUserRepository
     * @param AdminRoleRepository $adminRoleRepository
     * @param AdminPermissionRepository $adminPermissionRepository
     * @param AdminMenuRepository $adminMenuRepository
     * @param LogLoginRepository $logLoginRepository
     */
    public function __construct(AdminUserRepository $adminUserRepository, AdminRoleRepository $adminRoleRepository, AdminPermissionRepository $adminPermissionRepository, AdminMenuRepository $adminMenuRepository, LogLoginRepository $logLoginRepository)
    {
        $this->adminUserRepository = $adminUserRepository;
        $this->adminRoleRepository = $adminRoleRepository;
        $this->adminPermissionRepository = $adminPermissionRepository;
        $this->adminMenuRepository = $adminMenuRepository;
        $this->logLoginRepository = $logLoginRepository;
    }

    /**
     * ????????????
     *
     * @param string $username ?????????
     * @param string $password ??????
     * @param string $captchaCode ?????????
     * @param string $authCode ?????????
     * @param int $ip IP??????
     *
     * @throws LoginException
     * @throws ReflectionException
     * @throws Exception
     */
    public function login(string $username, string $password, string $captchaCode, string $authCode, int $ip)
    {
        $data = [
            'username' => $username,
            'ip' => $ip,
            'session' => session()->getId(),
        ];

        // ???????????????
        if (!check_vCode($captchaCode)) {
            $data['status'] = LogLoginStatusEnum::getName(LogLoginStatusEnum::CAPTCHA_ERROR);
            $this->logLoginRepository->create($data);
            throw new LoginException('???????????????');
        }

        $adminUser = $this->adminUserRepository->getByUsername($username);

        // ???????????????
        if (!empty($adminUser->token) && !TwoFactorAuthenticationUtil::verifyCode($adminUser->token, $authCode)) {
            $data['status'] = LogLoginStatusEnum::getName(LogLoginStatusEnum::AUTH_CODE_ERROR);
            $this->logLoginRepository->create($data);
            throw new LoginException('???????????????');
        }

        // ????????????
        if (empty($adminUser) || !password_verify($password, $adminUser->password)) {
            $data['status'] = LogLoginStatusEnum::getName(LogLoginStatusEnum::PASSWORD_ERROR);
            $this->logLoginRepository->create($data);
            throw new LoginException('????????????????????????');
        }

        // ????????????
        if ($adminUser->status == AdminUserStatusEnum::getName(AdminUserStatusEnum::FORBID)) {
            $data['status'] = LogLoginStatusEnum::getName(LogLoginStatusEnum::FORBIDDEN);
            $this->logLoginRepository->create($data);
            throw new LoginException('???????????????');
        }

        session()->put('admin_user_id', $adminUser->admin_user_id);
        session()->put('admin_user_name', $adminUser->name);
        if ($adminUser->admin_user_id == 1) {
            session()->put('system_user', 1);
        }
        $this->setAdminUserInfo($adminUser);

        $data['status'] = LogLoginStatusEnum::getName(LogLoginStatusEnum::LOGIN_SUCCESS);
        $userData = [
            'login_ip' => $ip,
            'login_time' => date('Y-m-d H:i:s'),
        ];
        $this->adminUserRepository->updateByAdminUserId($adminUser->admin_user_id, $userData);
        $this->logLoginRepository->create($data);
    }

    /**
     * ?????????????????????
     *
     * @throws Exception
     */
    public function refreshAdmin()
    {
        $adminUserId = session()->get('admin_user_id');
        $adminUser = $this->adminUserRepository->getByAdminUserId($adminUserId);
        if (empty($adminUser)) {
            session()->flush();
            return;
        }

        session()->put('admin_user_id', $adminUser->admin_user_id);
        session()->put('admin_user_name', $adminUser->name);
        $this->setAdminUserInfo($adminUser);
    }

    /**
     * ????????????
     *
     * @param $adminUsername
     *
     * @throws DataNotFoundException
     * @throws PermissionDenyException
     * @throws Exception
     */
    public function switchAdminUser($adminUsername) {
        if (session()->get('system_user') != 1) {
            throw new PermissionDenyException();
        }
        if (env('APP_ENV') == 'pre' || env('APP_ENV') == 'pdt') {
            throw new PermissionDenyException();
        }
        $adminUser = $this->adminUserRepository->getByUsername($adminUsername);
        if (empty($adminUser)) {
            throw new DataNotFoundException('???????????????????????????');
        }

        session()->put('admin_user_id', $adminUser->admin_user_id);
        session()->put('admin_user_name', $adminUser->name);
        $this->setAdminUserInfo($adminUser);
    }

    /**
     * ???????????????????????????
     *
     * @param AdminUser $adminUser
     *
     * @throws Exception
     */
    private function setAdminUserInfo(AdminUser $adminUser)
    {
        // ????????????
        $role = $this->getRole($adminUser);
        session()->put('admin_user_role_ids', $role['role_id']);
        session()->put('admin_user_role', implode(' ', $role['name']));
        session()->put('role_alias_grant', $role['alias']);

        // ????????????
        $permission = $this->getPermission($adminUser, $role['role_id']);
        session()->put('permission_alias', [
            'grant' => $permission['alias']['permission'] ?? [],
            'forbid' => $permission['alias']['forbid'] ?? [],
        ]);
        session()->put('permission_path', [
            'grant' => $permission['permission'] ?? [],
            'forbid' => $permission['forbid'] ?? [],
        ]);

        $adminInfo = json_encode([
            'session' => session()->getId(),
            'needRefresh' => 0
        ]);
        $prefix = env('APP_NAME');
        redis()->setex($prefix . "_admin_user_id_".$adminUser->admin_user_id, 7 *24 *60 *60, $adminInfo);


        $menu = $this->setMenu();
        session()->put('menu', $menu);
    }

    /**
     * ?????????????????????????????????
     *
     * @param AdminUser $adminUser
     *
     * @return array
     */
    public function getRole(AdminUser $adminUser)
    {
        $role = [
            'role_id' => [],
            'alias'   => [],
            'name'    => [],
        ];

        // ??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
        $userRole = $adminUser->role_grant;
        $userRole = $userRole->toArray();
        if (empty($userRole)) {
            return $role;
        }
        $rolePIds = array_column($userRole, 'parent_role_id');
        foreach ($userRole as $key => $value) {
            if (in_array($value['role_id'], $rolePIds)) {
                unset($userRole[$key]);
            }
        }

        foreach ($userRole as $item) {
            $role['role_id'][] = $item['role_id'];
            $role['alias'][] = $item['alias'];
            $role['name'][] = $item['name'];
        }
        return $role;
    }

    /**
     * ??????????????????
     *
     * @param AdminUser $adminUser
     * @param array     $roleIds
     *
     * @return array
     */
    private function getPermission(AdminUser $adminUser, array $roleIds)
    {
        // ????????????????????????
        $permissionGrant = $adminUser->permission_grant;
        $permissionForbid = $adminUser->permission_forbid;
        $role = $this->adminRoleRepository->listByRoleIds($roleIds)->toArray();
        $role = array_column($role, 'permission_grant');

        // ?????????????????????????????????????????????
        $grantList = [];
        $forbidList = [];
        foreach ($permissionGrant->toArray() as $permission) {
            $grantList[$permission['permission_id']] = $permission;
        }
        foreach ($role as $item) {
            foreach ($item as $permission) {
                $grantList[$permission['permission_id']] = $permission;
            }
        }
        foreach ($permissionForbid->toArray() as $permission) {
            $forbidList[$permission['permission_id']] = $permission;
            unset($grantList[$permission['permission_id']]);
        }

        // ????????????????????????
        $alias = [];         // ????????????
        $permissions = [];   // ????????????
        $forbid = [];        // ????????????
        foreach ($grantList as $permission) {
            $alias['permission'][] = $permission['alias'];
            $methods = explode(',', $permission['method']);
            foreach ($methods as $method) {
                $permissions[$method] = array_merge($permissions[$method] ?? [], explode(',', $permission['path']));
            }
        }
        foreach ($forbidList as $permission) {
            $alias['forbid'][] = $permission['alias'];
            $methods = explode(',', $permission['method']);
            foreach ($methods as $method) {
                $forbid[$method] = array_merge($forbid[$method] ?? [], explode(',', $permission['path']));
            }
        }

        return [
            'alias' => $alias,
            'permission' => $permissions,
            'forbid' => $forbid
        ];
    }

    /**
     * ????????????
     * $item = [
     *   'id' => 1,
     *   'title' => '??????',
     *   'icon' => 'fa-home',
     *   'active' => ['/system', '/admin'],
     *   'url' => '/system/index',
     *   'children' => []
     * ]
     *
     * @return mixed
     */
    private function setMenu()
    {
        // ?????????????????????
        $menu = $this->filterMenu();
        // ???????????????????????????
        $menu = $this->menuToTree($menu);
        return $menu['children'];
    }

    /**
     * ????????????
     *
     * @return array
     */
    private function filterMenu()
    {
        $menu = $this->adminMenuRepository->listAll()->toArray();
        foreach ($menu as $key => &$value) {
            if (empty($value['path'])) {
                continue;
            }
            if (!Permission::checkPath($value['path'])) {
                unset($menu[$key]);
            }
        }
        return $menu;
    }

    /**
     * ???????????????????????????
     *
     * @param array $menu
     * @param array $menuList
     * @param int $id
     *
     * @return array
     */
    private function menuToTree(&$menu, &$menuList = [], $id = 0)
    {
        if ($id == 0) {
            $menuList['active'] = $menuList['active'] ?? [];
            $menuList['children'] = $menuList['children'] ?? [];
        }
        foreach ($menu as $key => $value) {
            if ($value['parent_menu_id'] == $id) {
                $menuList['children'][$value['menu_id']] = [
                    'id' => $value['menu_id'],
                    'title' => $value['title'],
                    'icon' => $value['icon'],
                    'active' => [trim($value['path'], '/')],
                    'url' => trim($value['path'], '/'),
                    'children' => [],
                ];
                $menuList['active'][] = trim($value['path'], '/');
                unset($menu[$key]);
                $this->menuToTree($menu, $menuList['children'][ $value['menu_id']], $value['menu_id']);
                if (empty($menuList['children'][$value['menu_id']]['children']) && empty($menuList['children'][$value['menu_id']]['url'])) {
                    unset($menuList['children'][$value['menu_id']]);
                } else {
                    $menuList['active'] = array_merge($menuList['active'], $menuList['children'][$value['menu_id']]['active']);
                    $menuList['active'] = array_unique($menuList['active']);
                    $menuList['active'] = array_diff($menuList['active'], [""]);
                }
            }
        }
        return $menuList;
    }

    /**
     * ??????jwt??????
     *
     * @param string $jwt
     *
     * @throws DataNotFoundException
     * @throws PermissionDenyException
     * @throws ReflectionException
     * @throws Exception
     */
    public function loginWithJwtToken(string $jwt)
    {
        // TODO ??????issuer
        $audience = env('APP_NAME');
        if (!JwtHelper::verifyToken($jwt) || !JwtUtil::validate($jwt, null, $audience)) {
            throw new PermissionDenyException();
        }

        $adminUserId = JwtUtil::getClaim($jwt, 'issuer_admin_user_id');
        if (empty($adminUserId)) {
            throw new DataNotFoundException('????????????????????????');
        }

        $adminUser = $this->adminUserRepository->getByAdminUserId($adminUserId);

        // ????????????
        if ($adminUser->status == AdminUserStatusEnum::getName(AdminUserStatusEnum::FORBID)) {
            $data['status'] = LogLoginStatusEnum::getName(LogLoginStatusEnum::FORBIDDEN);
            $this->logLoginRepository->create($data);
            throw new PermissionDenyException();
        }

        session()->put('admin_user_id', $adminUser->admin_user_id);
        session()->put('admin_user_name', $adminUser->name);
        if ($adminUser->admin_user_id == 1) {
            session()->put('system_user', 1);
        }
        $this->setAdminUserInfo($adminUser);

        $data['status'] = LogLoginStatusEnum::getName(LogLoginStatusEnum::LOGIN_SUCCESS);
        $userData = [
            'login_ip' => IpUtil::getCurrentIP(),
            'login_time' => date('Y-m-d H:i:s'),
        ];
        $this->adminUserRepository->updateByAdminUserId($adminUser->admin_user_id, $userData);
        $this->logLoginRepository->create($data);
    }
    
    /**
     * ????????????Jwt
     *
     * @param string $audience
     *
     * @return String
     */
    public function generateLoginJwtToken(string $audience)
    {
        $audience = strtolower($audience);
        $param = [
            'type' => 'JWT_LOGIN',
            'issuer_admin_user_id' => session()->get('admin_user_id')
        ];
        $jwt = JwtHelper::generateToken($audience, null, 0, 60, $param);
        return $jwt;
    }
}