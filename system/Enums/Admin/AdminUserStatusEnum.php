<?php

namespace System\Enums\Admin;

use AiLeZai\Lumen\Framework\Enums\BaseEnum;

class AdminUserStatusEnum extends BaseEnum
{
    const NORMAL = ['NORMAL', '正常'];
    const FORBID = ['FORBID', '禁用'];
}