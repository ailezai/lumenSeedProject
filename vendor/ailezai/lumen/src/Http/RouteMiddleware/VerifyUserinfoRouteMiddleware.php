<?php

namespace AiLeZai\Lumen\Framework\Http\RouteMiddleware;

use Exception;
use AiLeZai\Lumen\Framework\Exceptions\VerifyException;
use AiLeZai\Common\Lib\Jwt\Api\UserAuthData;
use Closure;
use AiLeZai\Common\Lib\Jwt\UserInfoHelper;

/**
 * userInfo校验
 * 若校验不通过则直接抛出异常结束
 *
 * Class UserInfoRouteMiddleware
 * @package App\Http\RouteMiddleware
 */
class VerifyUserinfoRouteMiddleware
{
    public static $alias = 'verify.userinfo';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function handle($request, Closure $next)
    {
        $userInfo = $request->input('userinfo', null);
        if (!empty($userInfo)) {
            $user = UserInfoHelper::decrypt($userInfo);
            if (!empty($user)) {
                UserAuthData::initByJjbUserinfo($user[0], $user[1]);
            } else {
                throw new VerifyException("登录已过期，请重新登录", 1401);
            }
        } else {
            throw new VerifyException("登录已过期，请重新登录", 1401);
        }

        return $next($request);
    }
}
