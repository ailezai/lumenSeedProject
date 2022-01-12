<?php
namespace System\Presenters\Log;

class LogSqlPresenter
{
    /**
     * 格式化输出SQL耗时，超过100ms则标红加粗
     *
     * @param $time
     *
     * @return string
     */
    public function formatTime($time)
    {
        if ($time > 100) {
            return '<span style="color: #FF0000;font-weight: bolder">'.$time.'</span>';
        }
        return $time;
    }
}