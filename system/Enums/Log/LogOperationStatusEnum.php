<?php

namespace System\Enums\Log;

use AiLeZai\Lumen\Framework\Enums\BaseEnum;

class LogOperationStatusEnum extends BaseEnum
{
    const UNKNOWN = ['UNKNOWN', '未知'];
    const OPERATION_SUCCESS = ['OPERATION_SUCCESS', '操作成功'];
    const OPERATION_FAIL = ['OPERATION_FAIL', '操作失败'];
}