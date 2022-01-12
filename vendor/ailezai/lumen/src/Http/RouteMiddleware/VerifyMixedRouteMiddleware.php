<?php

namespace AiLeZai\Lumen\Framework\Http\RouteMiddleware;

use AiLeZai\Common\Lib\Jwt\Api\JwtHelper;
use AiLeZai\Common\Lib\Log\LOG;
use AiLeZai\Lumen\Framework\Exceptions\VerifyException;
use AiLeZai\Common\Lib\Jwt\Api\UserAuthData;
use Closure;
use Exception;
use AiLeZai\Common\Lib\Jwt\UserInfoHelper;
use AiLeZai\Common\Lib\RPC\HttpServiceClient;

/**
 * token 和 userInfo 混合校验，优先使用token方案
 * 若校验不通过则直接抛出异常结束
 *
 * Class MixedRouteMiddleware
 * @package App\Http\RouteMiddleware
 */
class VerifyMixedRouteMiddleware
{
    public static $alias = 'verify.mixed';

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
        $userInfo = $request->input('userinfo', null);
        if (!JwtHelper::isInitTokenNull()) {
            $this->dealToken($userInfo);
        } elseif (!empty($userInfo)) {
            $this->dealUserinfo($userInfo);
        } else {
            throw new VerifyException("登录已过期，请重新登录", 1401);
        }

        return $next($request);
    }

    /**
     * 解析token
     *
     * @param $userInfo
     *
     * @throws VerifyException
     */
    private function dealToken($userInfo)
    {
        JwtHelper::initByAuthHeader();
        try {
            if (JwtHelper::getToken() === "") {
                $user = UserInfoHelper::decrypt($userInfo);
                if (!empty($user)) {
                    $data = [
                        'axUid' => $user[0]
                    ];
                    $token = HttpServiceClient::callAxUser("user/makeUpToken", $data);
                    JwtHelper::initToken($token);
                    return;
                }
            }
        } catch (Exception $e) {
            LOG::error(LOG::e2str($e));
        }

        throw new VerifyException("登录已过期，请重新登录", 1401);
    }

    /**
     * 解析userinfo逻辑
     *
     * @param $userInfo
     *
     * @throws Exception
     */
    private function dealUserinfo($userInfo)
    {
        $user = UserInfoHelper::decrypt($userInfo);
        if (!empty($user)) {
            UserAuthData::initByJjbUserinfo($user[0], $user[1]);
            return;
        }

        throw new VerifyException("登录已过期，请重新登录", 1401);
    }
}
