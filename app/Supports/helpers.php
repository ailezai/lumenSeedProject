<?php

use App\Supports\CommonUtil;
use App\Supports\WhereFilter;

if (!function_exists('sql_where')) {

    /**
     * get sql_where_filter instance.
     *
     * @return \App\Supports\WhereFilter
     */
    function sql_where()
    {
        return app()->make(WhereFilter::class);
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

if (!function_exists('auto_asset')) {

    /**
     * Generate a url for the application.
     *
     * @param  string  $path
     * @return string
     */
    function auto_asset($path = null)
    {
        return app('url')->asset($path, CommonUtil::isHttps());
    }
}