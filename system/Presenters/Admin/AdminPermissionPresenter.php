<?php

namespace System\Presenters\Admin;

class AdminPermissionPresenter
{
    /**
     * 格式化请求方法
     *
     * @param $method
     * @return string
     */
    public function formatMethod($method)
    {
        $method = explode(',', $method);
        $content = '';
        foreach ($method as $item) {
            $content .= '<span class="label label-success">'.$item.'</span>';
        }
        return $content;
    }

    /**
     * 格式化路由
     *
     * @param $path
     *
     * @return string
     */
    public function formatPath($path)
    {
        $path = explode(',', $path);
        $content = '';
        foreach ($path as $item) {
            $content .= '<code>'.$item.'</code><br/>';
        }
        return $content;
    }

    /**
     * 格式化请求方法+路由
     *
     * @param $method
     * @param $path
     *
     * @return string
     */
    public function formatRequest($method, $path)
    {
        $method = explode(',', $method);
        $methods = '';
        foreach ($method as $item) {
            $methods .= '<span class="label label-success">'.$item.'</span>';
        }

        $path = explode(',', $path);
        $content = '';
        foreach ($path as $item) {
            $content .= $methods.'<code>'.$item.'</code><br/>';
        }
        return $content;
    }
}