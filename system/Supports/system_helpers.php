<?php
use App\Supports\CommonUtil;

if (! function_exists('get_active_menus')) {

    /**
     * get active menus
     *
     * @param array $active
     *
     * @return bool
     */
    function get_active_menus($active = [])
    {
        $activeUri = app()->make('request')->path();
        $activeUri = trim($activeUri, '/');
        if (in_array($activeUri, $active)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('auto_url')) {

    /**
     * Generate a url for the application.
     *
     * @param  string  $path
     * @param  mixed   $parameters
     * @return string
     */
    function auto_url($path = null, $parameters = [])
    {
        return app('url')->to($path, $parameters, CommonUtil::isHttps());
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    function csrf_token()
    {
        $session = app('session');

        if (isset($session)) {
            return $session->token();
        }

        throw new RuntimeException('Application session store not set.');
    }
}