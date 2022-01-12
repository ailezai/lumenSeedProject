<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2018/1/7
 */

namespace System\Http\RouteMiddleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use System\Exceptions\PermissionDenyException;
use System\Supports\Permission;

class PermissionRouteMiddle
{
    /**
     * route middleware alias
     *
     * @var string
     */
    public static $alias = 'admin.permission';


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws PermissionDenyException
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $prefix = env('APP_NAME');
        $adminUserId = session()->get('admin_user_id');
        $adminUserInfo = redis()->get($prefix . "_admin_user_id_{$adminUserId}");
        $adminUserInfo = json_decode($adminUserInfo, true);

        // 缺少登录信息或信息不匹配，返回登录页
        if (empty($adminUserId) || empty($adminUserInfo)) {
            session()->forget('admin_user_id');
            $preAction = [
                'method' => 'notify',
                'message' => '登录过期，请重新登录',
                'type' => 'error'
            ];
            session()->put('__preAction', $preAction);
            return redirect('login');
        }
        if (session()->getId() != $adminUserInfo['session'] && session()->get('system_user') != 1) {
            session()->flush();
            $preAction = [
                'method' => 'notify',
                'message' => '异地登录',
                'type' => 'error'
            ];
            session()->put('__preAction', $preAction);
            return redirect('login');
        }

        // 权限校验，是否需要热更新
        if ($adminUserInfo['needRefresh'] == 1) {
            $loginService = app()->make('System\Services\LoginService');
            $loginService->refreshAdmin();
        }

        // 路由鉴权，是否允许
        if (!Permission::checkPath($request->path(), $request->method())) {
            throw new PermissionDenyException();
        }
        return $next($request);
    }
}