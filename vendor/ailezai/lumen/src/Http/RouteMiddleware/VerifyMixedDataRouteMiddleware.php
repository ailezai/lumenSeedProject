<?php

namespace AiLeZai\Lumen\Framework\Http\RouteMiddleware;

use Closure;
use Exception;
use AiLeZai\Common\Lib\Jwt\Api\JwtHelper;
use AiLeZai\Common\Lib\Jwt\UserInfoHelper;
use AiLeZai\Common\Lib\Log\LOG;
use AiLeZai\Common\Lib\RPC\HttpServiceClient;
use AiLeZai\Common\Lib\Jwt\Api\UserAuthData;

/**
 * token 和 userInfo 混合校验，优先使用token方案
 * 仅获取数据
 *
 * Class MixedRouteMiddleware
 * @package App\Http\RouteMiddleware
 */
class VerifyMixedDataRouteMiddleware
{
    public static $alias = 'verify.mixed_data';

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
        JwtHelper::initByAuthHeader();
        $userInfo = $request->input('userinfo', null);
        if (!JwtHelper::isInitTokenNull()) {
            $this->dealToken($userInfo);
        } elseif (!empty($userInfo)) {
            $this->dealUserinfo($userInfo);
        }

        return $next($request);
    }

    /**
     * 解析token
     *
     * @param $userInfo
     */
    private function dealToken($userInfo)
    {
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
    }

    /**
     * 解析userinfo逻辑
     *
     * @param $userInfo
     */
    private function dealUserinfo($userInfo)
    {
        $user = UserInfoHelper::decrypt($userInfo);
        if (!empty($user)) {
            UserAuthData::initByJjbUserinfo($user[0], $user[1]);
            return;
        }
    }
}
