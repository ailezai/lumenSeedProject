<?php

namespace System\Http\Controllers\Log;

use Illuminate\Http\Request;
use Illuminate\View\View;
use ReflectionException;
use System\Enums\Log\LogLoginStatusEnum;
use System\Services\Log\LogLoginService;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;

class LogLoginController extends BaseController
{
    /**
     * @var LogLoginService
     */
    protected $logLoginService;

    /**
     * LogLoginController constructor.
     *
     * @param LogLoginService $logLoginService
     */
    public function __construct(LogLoginService $logLoginService)
    {
        $this->logLoginService = $logLoginService;
    }

    /**
     * 登录日志
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws ReflectionException
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'ip' => 'string',
            'status' => 'string'
        ]);
        $condition['ip'] = $request->input('ip', null);
        $condition['status'] = $request->input('status', null);
        $list = $this->logLoginService->listPaginateByCondition($condition)->appends($request->all());
        return view('_system.log.login')
            ->with('request', $request)
            ->with('loginStatus', LogLoginStatusEnum::getAllConst())
            ->with('list', $list);
    }
}