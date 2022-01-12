<?php

namespace System\Presenters\Admin;

use ReflectionException;
use System\Enums\Admin\AdminRoleTypeEnum;

class AdminRolePresenter
{
    /**
     * 格式化类别
     *
     * @param string $type
     *
     * @return string
     *
     * @throws ReflectionException
     */
    public function formatType(string $type)
    {
        $type = AdminRoleTypeEnum::getDesc($type);
        if (empty($type)) {
            return '<span class="label label-default">未知</span>';
        }
        return '<span class="label label-success">'.$type.'</span>';
    }
}