<?php
namespace System\Services\Personal;

use System\Exceptions\PermissionDenyException;
use System\Repositories\Admin\AdminUserRepository;

class PersonalService
{
    /**
     * @var AdminUserRepository
     */
    protected $adminUserRepository;

    /**
     * PersonalService constructor.
     * @param AdminUserRepository $adminUserRepository
     */
    public function __construct(AdminUserRepository $adminUserRepository)
    {
        $this->adminUserRepository = $adminUserRepository;
    }

    /**
     * 修改密码
     *
     * @param string $oldPassword
     * @param string $password
     *
     * @throws PermissionDenyException
     */
    public function resetPassword(string $oldPassword, string $password)
    {
        $adminUserId = session()->get('admin_user_id');
        $adminUser = $this->adminUserRepository->getByAdminUserId($adminUserId);
        if (!password_verify($oldPassword, $adminUser->password)) {
            throw new PermissionDenyException('密码错误');
        }
        $password = password_hash($password, PASSWORD_BCRYPT);
        $this->adminUserRepository->updateByAdminUserId($adminUserId, ['password' => $password]);
    }

    /**
     * 修改个人信息
     *
     * @param array $params
     */
    public function submitInfo(array $params)
    {
        $adminUserId = session()->get('admin_user_id');
        $data = [
            'name' => $params['name'],
            'mobile' => $params['mobile'],
            'mail' => $params['mail']
        ];
        $this->adminUserRepository->updateByAdminUserId($adminUserId, $data);
        session()->put('admin_user_name', $params['name']);
    }
}