<?php

namespace AiLeZai\Lumen\Framework\Http\RouteMiddleware;

use AiLeZai\Common\Lib\Jwt\Api\UserAuthData;
use Closure;
use AiLeZai\Common\Lib\Jwt\UserInfoHelper;

/**
 * userInfo校验
 * 仅获取数据
 *
 * Class UserInfoRouteMiddleware
 * @package App\Http\RouteMiddleware
 */
class VerifyUserinfoDataRouteMiddleware
{
    public static $alias = 'verify.userinfo_data';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $userInfo = $request->input('userinfo', null);
        if (!empty($userInfo)) {
            $user = UserInfoHelper::decrypt($userInfo);
            if (!empty($user)) {
                UserAuthData::initByJjbUserinfo($user[0], $user[1]);
            }
        }

        return $next($request);
    }
}
