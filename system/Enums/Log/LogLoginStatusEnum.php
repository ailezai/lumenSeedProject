<?php

namespace System\Enums\Log;

use AiLeZai\Lumen\Framework\Enums\BaseEnum;

class LogLoginStatusEnum extends BaseEnum
{
    const UNKNOWN = ['UNKNOWN', '未知'];
    const CAPTCHA_ERROR = ['CAPTCHA_ERROR', '验证码错误'];
    const AUTH_CODE_ERROR = ['AUTH_CODE_ERROR', '授权码错误'];
    const PASSWORD_ERROR = ['PASSWORD_ERROR', '密码错误'];
    const FORBIDDEN = ['FORBIDDEN', '账户已禁用'];
    const LOGIN_SUCCESS = ['LOGIN_SUCCESS', '登录成功'];
}