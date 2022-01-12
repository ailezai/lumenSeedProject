<?php
namespace System\Supports;
use Exception;
use System\Models\Admin\AdminPermission;
use System\Models\Admin\AdminRole;

/**
 * 权限热更新
 *
 * Class HotRefresh
 * @package System\Supports
 */
class HotRefresh
{
    /**
     * 刷新所有用户
     *
     * @throws Exception
     */
    public static function refreshAll()
    {
        $prefix = env('APP_NAME');
        $adminInfo = redis()->command('keys', [$prefix . '_admin_user_id_*']);
        foreach ($adminInfo as $item) {
            $item = str_replace($prefix . '_admin_user_id_', '', $item);
            static::refresh($item);
        }
    }

    /**
     * 根据admin_user_id更新
     *
     * @param integer $userId
     */
    public static function refreshByUserId(int $userId)
    {
        static::refresh($userId);
    }

    /**
     * 根据admin_user_id更新
     *
     * @param array $userIds
     */
    public static function refreshByUserIds(array $userIds)
    {
        foreach ($userIds as $item) {
            static::refresh($item);
        }
    }

    /**
     * 根据权限role_id更新权限
     *
     * @param int $roleId
     * @param bool $refreshNow
     *
     * @return array
     */
    public static function refreshByRoleId(int $roleId, bool $refreshNow = true)
    {
        $role = AdminRole::select('role_id')
            ->with('users')
            ->where('role_id', $roleId)
            ->first()
            ->toArray();

        $userIds = array_column($role['users'], 'admin_user_id');

        if ($refreshNow) {
            static::refreshByUserIds($userIds);
        }

        return $userIds;
    }

    /**
     * 根据权限permission_id更新权限
     *
     * @param integer $permissionId
     * @param bool    $refreshNow
     *
     * @return array
     */
    public static function refreshByPermissionId(int $permissionId, bool $refreshNow = true)
    {
        $userGrant = AdminPermission::select('permission_id')
            ->with('roles')
            ->with('user_grant')
            ->with('user_forbid')
            ->where('permission_id', $permissionId)
            ->first()
            ->toArray();
        $adminUserId = array_merge(array_column($userGrant['user_grant'], 'admin_user_id'), array_column($userGrant['user_forbid'], 'admin_user_id'));

        $roleIds = array_column($userGrant['roles'], 'role_id');
        $role = AdminRole::select('role_id')
            ->with('users')
            ->whereIn('role_id', $roleIds)
            ->get()
            ->toArray();
        if (!empty($role)) {
            $users = array_column($role, 'users');
            foreach ($users as $user) {
                $adminUserId = array_merge($adminUserId, array_column($user, 'admin_user_id'));
            }
        }

        $adminUserIds = array_unique($adminUserId);
        if ($refreshNow) {
            static::refreshByUserIds($adminUserIds);
        }

        return $adminUserId;
    }

    /**
     * 根据管理员id刷新
     *
     * @param string $adminUserId
     *
     * @throws Exception
     */
    private static function refresh(string $adminUserId)
    {
        $prefix = env('APP_NAME');
        $key = $prefix . "_admin_user_id_{$adminUserId}";
        if (!redis()->exists($key)) {
            return;
        }
        $adminInfo = redis()->get($key);
        $ttl = redis()->command('ttl', [$key]);
        $adminInfo = json_decode($adminInfo, true);
        $adminInfo['needRefresh'] = 1;
        $adminInfo = json_encode($adminInfo);
        redis()->setex($key, $ttl, $adminInfo);
    }
}