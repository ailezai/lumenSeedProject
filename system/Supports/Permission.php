<?php
namespace System\Supports;

class Permission
{
    /**
     * 角色检验
     *
     * @param $str
     *
     * @return bool
     */
    public static function checkRole($str)
    {
        $role = session()->get('role_alias_grant');
        $str = explode('.', $str);
        foreach ($role as $item) {
            $item = explode('.', $item);
            if (count($str) >= count($item)) {
                $size = count($item);
            } else {
                continue;
            }
            for ($i = 0; $i < $size; $i++) {
                if (($item[$i] == '*')
                    || ($item[$i] == $str[$i] && $i == $size - 1)) {
                    return true;
                }
                if ($item[$i] != $str[$i]) {
                    break;
                }
            }
        }
        return false;
    }

    /**
     * 权限检验
     *
     * @param $str
     *
     * @return bool
     */
    public static function checkPermission($str)
    {
        $permission = session()->get('permission_alias');
        $permission = $permission['grant'];
        foreach ($permission as $item) {
            $item = explode('.', $item);
            if (count($str) >= count($item)) {
                $size = count($item);
            } else {
                continue;
            }
            for ($i = 0; $i < $size; $i++) {
                if (($item[$i] == '*')
                    || ($item[$i] == $str[$i] && $i == $size - 1)) {
                    return true;
                }
                if ($item[$i] != $str[$i]) {
                    break;
                }
            }
        }
        return false;
    }

    /**
     * 路由访问检验
     *
     * @param string $path   路由
     * @param string $method 方法
     *
     * @return bool
     */
    public static function checkPath(string $path, string $method = 'GET')
    {
        // 获取用户的权限
        $permissionList = session()->get('permission_path');
        $permission = array_merge($permissionList['grant']['ALL'] ?? [], $permissionList['grant'][$method] ?? []);
        $forbidden = array_merge($permissionList['forbid']['ALL'] ?? [], $permissionList['forbid'][$method] ?? []);

        $path = explode('/', trim($path, '/'));
        // 遇到禁用授权，直接false
        foreach ($forbidden as $item) {
            $item = explode('/', trim($item, '/'));
            $size = count($path) < count($item) ? count($path) : count($item);
            for ($i = 0; $i < $size; $i++) {
                if (($item[$i] == '*') && empty($item[$i + 1])) {
                    return false;
                } else if ($item[$i] == '*') {
                    continue;
                }
                if ($item[$i] != $path[$i]) {
                    break;
                }
                if ($item[$i] == $path[$i] && empty($item[$i + 1]) && empty($path[$i + 1])) {
                    return false;
                }
            }
        }

        // 遇到允许授权，返回true
        foreach ($permission as $item) {
            $item = explode('/', trim($item, '/'));
            $size = count($path) < count($item) ? count($path) : count($item);
            for ($i = 0; $i < $size; $i++) {
                if (($item[$i] == '*') && empty($item[$i + 1])) {
                    return true;
                } else if ($item[$i] == '*') {
                    continue;
                }
                if ($item[$i] != $path[$i]) {
                    break;
                }
                if ($item[$i] == $path[$i] && empty($item[$i + 1]) && empty($path[$i + 1])) {
                    return true;
                }
            }
        }

        return false;
    }
}