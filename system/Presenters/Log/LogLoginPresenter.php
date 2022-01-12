<?php
namespace System\Presenters\Log;

use ReflectionException;
use System\Enums\Log\LogLoginStatusEnum;

class LogLoginPresenter
{
    /**
     * 格式化状态输出
     *
     * @param $status
     *
     * @return string
     *
     * @throws ReflectionException
     */
    public function formatStatus($status)
    {
        $desc = LogLoginStatusEnum::getDesc($status);
        switch ($status) {
            case LogLoginStatusEnum::getName(LogLoginStatusEnum::LOGIN_SUCCESS):
                return '<span class="label label-info">'.$desc.'</span>';
            default:
                return '<span class="label label-danger">'.$desc.'</span>';
                break;
        }
    }
}