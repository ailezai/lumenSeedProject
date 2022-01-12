<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2018/1/7
 */

namespace System\Http\RouteMiddleware;

use Illuminate\Http\Request;
use System\Exceptions\PermissionDenyException;

class LoginRouteMiddle
{
    /**
     * route middleware alias
     *
     * @var string
     */
    public static $alias = 'admin.login';


    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $adminUserId = session()->get('admin_user_id');
        if (empty($adminUserId)) {
            return redirect('login');
        }

        return $next($request);
    }
}