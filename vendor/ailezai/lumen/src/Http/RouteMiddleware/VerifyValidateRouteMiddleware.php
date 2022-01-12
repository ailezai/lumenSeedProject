<?php
namespace AiLeZai\Lumen\Framework\Http\RouteMiddleware;

use Closure;

/**
 * 数据有效性校验，必须置于其他verify之后
 * 校验内容：
 *   1. 登录是否过期
 *
 * Class ValidateRouteMiddleware
 * @package App\Http\RouteMiddleware\Verify
 */
class VerifyValidateRouteMiddleware
{
    public static $alias = 'verify.validate';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}