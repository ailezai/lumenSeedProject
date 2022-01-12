<?php
namespace System\Presenters\Log;

use ReflectionException;
use System\Enums\Log\LogOperationStatusEnum;

class LogOperationPresenter
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
        $desc = LogOperationStatusEnum::getDesc($status);
        switch ($status) {
            case LogOperationStatusEnum::getName(LogOperationStatusEnum::OPERATION_SUCCESS):
                return '<span class="label label-info">'.$desc.'</span>';
            default:
                return '<span class="label label-danger">'.$desc.'</span>';
                break;
        }
    }
}