<?php

namespace AiLeZai\Lumen\Framework;

use AiLeZai\Common\Lib\Config\CFG;
use Laravel\Lumen\Application as Container;
use Symfony\Component\Finder\Finder;

class Application extends Container
{
    /**
     * The custom environment path defined by the developer.
     *
     * @var string
     */
    protected $environmentPath;

    /**
     * The environment file to load during bootstrapping.
     *
     * @var string
     */
    protected $environmentFile = '.env';

    /**
     * Create a new Lumen application instance.
     *
     * @param  string|null  $basePath
     */
    public function __construct($basePath = null)
    {
        // 定义开始时间
        if (!defined('LUMEN_START_TIME')) {
            define('LUMEN_START_TIME', microtime(true));
        }

        parent::__construct($basePath);

        $this->registerRedis();

        $this->loadConfigurationFiles($this);
        $this->loadServiceProviderFiles($this);
        $this->loadGlobalMiddlewareFiles($this);
        $this->loadRouteMiddlewareFiles($this);

        $this->initCommonLib();
    }

    /**
     * 注册redis
     */
    protected function registerRedis()
    {
        $this->register(\Illuminate\Redis\RedisServiceProvider::class);
    }

    /**
     * @override
     *
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param  string|null  $name
     *
     * @return string
     */
    public function getConfigurationPath($name = null)
    {
        if (! $name) {
            $appConfigDir = $this->basePath('config').'/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__.'/../config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config').'/'.$name.'.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
                return $path;
            }
        }
        return '';
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param  Application $app
     *
     * @return void
     */
    protected function loadConfigurationFiles(Application $app)
    {
        // 用户的config先加载，用户的config和框架的config重名，会忽略框架的config
        $customFiles = Finder::create()->files()->name('*.php')->in($app->basePath('config'));
        $this->loadConfigureByFiles($app, $customFiles);

        $frameworkFiles = Finder::create()->files()->name('*.php')->in(__DIR__ . '/../config');
        $this->loadConfigureByFiles($app, $frameworkFiles);
    }

    /**
     * Load the configuration items by files.
     *
     * @param Application $app
     * @param array $files
     *
     * @return void
     */
    protected function loadConfigureByFiles(Application $app, $files = [])
    {
        foreach ($files as $file) {

            /**
             * @var \Symfony\Component\Finder\SplFileInfo $file
             */

            // 不加载 example
            if ($file->getFilename() == 'example.php') {
                continue;
            }

            $name = str_replace('.php', '', $file->getFilename());

            if (!empty($name)) {
                $app->configure($name);
            }
        }
    }

    /**
     * Load the service provider items from all of the files.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function loadServiceProviderFiles(Application $app)
    {
        // 用户的provider先注册，用户的provider和框架的provider重名，会忽略框架的provider
        $customFiles = Finder::create()->files()->name('*.php')->in($app->basePath('app/Providers'));
        $this->loadServiceProviderByFiles($app, $customFiles, 'App\Providers\\');

        $frameworkFiles = Finder::create()->files()->name('*.php')->in(__DIR__ . '/Providers');
        $this->loadServiceProviderByFiles($app, $frameworkFiles, 'AiLeZai\Lumen\Framework\Providers\\');
    }

    /**
     * Load the service provider items by files.
     *
     * @param Application $app
     * @param array $files
     * @param string $prefix
     *
     * @return void
     */
    protected function loadServiceProviderByFiles(Application $app, $files = [], $prefix = '\App\Providers\\')
    {
        foreach ($files as $file) {

            /**
             * @var \Symfony\Component\Finder\SplFileInfo $file
             */

            // 不加载 example
            if ($file->getFilename() == 'ExampleServiceProvider.php') {
                continue;
            }

            $class = $prefix . str_replace('.php', '', $file->getFilename());

            if (class_exists($class)) {
                $app->register($class);
            }
        }
    }

    /**
     * Load the global middleware items from all of the files.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function loadGlobalMiddlewareFiles(Application $app)
    {
        // 用户的middleware先添加，用户的middleware和框架的middleware重名，会忽略框架的middleware
        // middleware() 调用 array_unique，array_unique只保留第一个出现的数值
        $customFiles = Finder::create()->files()->name('*.php')->in($app->basePath('app/Http/Middleware'));
        $this->loadGlobalMiddlewareByFiles($app, $customFiles, 'App\Http\Middleware\\');

        $frameworkFiles = Finder::create()->files()->name('*.php')->in(__DIR__ . '/Http/Middleware');
        $this->loadGlobalMiddlewareByFiles($app, $frameworkFiles, 'AiLeZai\Lumen\Framework\Http\Middleware\\');
    }

    /**
     * Load the global middleware items by files.
     *
     * @param Application $app
     * @param array $files
     * @param string $prefix
     *
     * @return void
     */
    protected function loadGlobalMiddlewareByFiles(Application $app, $files = [], $prefix = '\App\Http\Middleware\\')
    {
        foreach ($files as $file) {

            /**
             * @var \Symfony\Component\Finder\SplFileInfo $file
             */

            // 不加载 example
            if ($file->getFilename() == 'ExampleMiddleware.php') {
                continue;
            }

            $class = $prefix . str_replace('.php', '', $file->getFilename());

            if (class_exists($class)) {
                $app->middleware([$class]);
            }
        }
    }

    /**
     * Load the route middleware items from all of the files.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function loadRouteMiddlewareFiles(Application $app)
    {
        // 框架的routeMiddleware先添加，用户的routeMiddleware和框架的routeMiddleware重名，会忽略框架的routeMiddleware
        // routeMiddleware() 调用 array_merge，array_merge会处理相同的字符串键名，则该键名后面的值将覆盖前一个值
        $frameworkFiles = Finder::create()->files()->name('*.php')->in(__DIR__ . '/Http/RouteMiddleware');
        $this->loadRouteMiddlewareByFiles($app, $frameworkFiles, 'AiLeZai\Lumen\Framework\Http\RouteMiddleware\\');

        $customFiles = Finder::create()->files()->name('*.php')->in($app->basePath('app/Http/RouteMiddleware'));
        $this->loadRouteMiddlewareByFiles($app, $customFiles, 'App\Http\RouteMiddleware\\');
    }

    /**
     * Load the route middleware items by files.
     *
     * @param Application $app
     * @param array $files
     * @param string $prefix
     *
     * @return void
     */
    protected function loadRouteMiddlewareByFiles(Application $app, $files = [], $prefix = '\App\Http\RouteMiddleware\\')
    {
        foreach ($files as $file) {

            /**
             * @var \Symfony\Component\Finder\SplFileInfo $file
             */

            // 不加载 example
            if ($file->getFilename() == 'ExampleRouteMiddleware.php') {
                continue;
            }

            $class = $prefix . str_replace('.php', '', $file->getFilename());

            if (class_exists($class) && property_exists($class, 'alias')) {
                $app->routeMiddleware([
                    $class::$alias => $class
                ]);
            }
        }
    }

    /**
     * 初始化common-lib
     */
    protected function initCommonLib()
    {
        CFG::mergeCFG(config('common'));
    }

    /**
     * Get the path to the environment file directory.
     *
     * @return string
     */
    public function environmentPath()
    {
        return $this->environmentPath ?: $this->basePath;
    }

    /**
     * Get the environment file the application is using.
     *
     * @return string
     */
    public function environmentFile()
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * Get the fully qualified path to the environment file.
     *
     * @return string
     */
    public function environmentFilePath()
    {
        return $this->environmentPath().'/'.$this->environmentFile();
    }
}