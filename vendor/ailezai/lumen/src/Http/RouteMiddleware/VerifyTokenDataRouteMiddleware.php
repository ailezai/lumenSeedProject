<?php

namespace AiLeZai\Lumen\Framework\Http\RouteMiddleware;

use AiLeZai\Common\Lib\Jwt\Api\JwtHelper;
use Closure;

/**
 * token校验
 * 仅获取数据
 *
 * Class TokenRouteMiddleware
 * @package App\Http\RouteMiddleware
 */
class VerifyTokenDataRouteMiddleware
{
    public static $alias = 'verify.token_data';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        JwtHelper::initByAuthHeader();

        return $next($request);
    }
}
