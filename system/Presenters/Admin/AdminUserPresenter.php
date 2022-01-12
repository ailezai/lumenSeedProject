<?php
namespace System\Presenters\Admin;

use ReflectionException;
use System\Enums\Admin\AdminUserStatusEnum;

class AdminUserPresenter
{
    /**
     * 格式化用户状态
     *
     * @param string $status 状态
     * @param integer $adminUserId 管理员id
     *
     * @return string
     *
     * @throws ReflectionException
     */
    public function formatStatus(string $status, int $adminUserId)
    {
        $statusDesc = AdminUserStatusEnum::getDesc($status);
        switch ($status) {
            case AdminUserStatusEnum::getName(AdminUserStatusEnum::NORMAL):
                return '<a class="btn btn-xs btn-success" href="'.auto_url('system/user/status?status=FORBID&admin_user_id='.$adminUserId).'" data-ajax-get="true">'.$statusDesc.'</a>';
            case AdminUserStatusEnum::getName(AdminUserStatusEnum::FORBID):
                return '<a class="btn btn-xs btn-danger" href="'.auto_url('system/user/status?status=NORMAL&admin_user_id='.$adminUserId).'" data-ajax-get="true">'.$statusDesc.'</a>';
            default:
                return '<span class="label label-default">未知</span>';
        }
    }
}