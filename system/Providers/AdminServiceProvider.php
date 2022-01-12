<?php
namespace System\Providers;

use AiLeZai\Lumen\Framework\Application;
use System\Http\Middleware\LogToDBMiddleware;
use System\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\CookieServiceProvider;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Support\ServiceProvider;
use System\Http\RouteMiddleware\LoginRouteMiddle;
use System\Http\RouteMiddleware\PermissionRouteMiddle;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        /**
         * @var Application $app
         */
        $app = $this->app;

        // cookie服务提供者
        $app->register(CookieServiceProvider::class);

        // 设置session别名
        $app->alias('session', 'Illuminate\Session\SessionManager');

        // session服务提供者
        $app->register(SessionServiceProvider::class);

        // session middleware
        $app->middleware([
            LogToDBMiddleware::class,
            StartSession::class,
            VerifyCsrfToken::class
        ]);

        // 权限管理
        $app->routeMiddleware([
            PermissionRouteMiddle::$alias => PermissionRouteMiddle::class,
            LoginRouteMiddle::$alias => LoginRouteMiddle::class
        ]);
    }
}