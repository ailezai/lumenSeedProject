<?php

namespace AiLeZai\Lumen\Framework\Http\RouteMiddleware;

use AiLeZai\Common\Lib\Jwt\Api\JwtHelper;
use AiLeZai\Lumen\Framework\Exceptions\VerifyException;
use AiLeZai\Common\Lib\Jwt\Api\UserAuthData;
use Closure;
use Exception;

/**
 * token校验
 * 若校验不通过则直接抛出异常结束
 *
 * Class TokenRouteMiddleware
 * @package App\Http\RouteMiddleware
 */
class VerifyTokenRouteMiddleware
{
    public static $alias = 'verify.token';

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
        JwtHelper::initByAuthHeader();

        if (empty(UserAuthData::getAxUid())) {
            throw new VerifyException("登录已过期，请重新登录", 1401);
        }

        return $next($request);
    }
}
