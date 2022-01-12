<?php


namespace System\Http\Controllers\Log;

use Illuminate\Http\Request;
use Illuminate\View\View;
use ReflectionException;
use System\Enums\Log\LogOperationStatusEnum;
use System\Services\Admin\AdminUserService;
use System\Services\Log\LogOperationService;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;

class LogOperationController extends BaseController
{
    /**
     * @var LogOperationService
     */
    protected $logOperationService;

    /**
     * @var AdminUserService
     */
    protected $adminUserService;

    /**
     * LogOperationController constructor.
     * @param LogOperationService $logOperationService
     * @param AdminUserService $adminUserService
     */
    public function __construct(LogOperationService $logOperationService, AdminUserService $adminUserService)
    {
        $this->logOperationService = $logOperationService;
        $this->adminUserService = $adminUserService;
    }

    /**
     * 操作日志
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
            'trace_id' => 'string',
            'admin_user_id' => 'integer',
            'ip' => 'string',
            'path' => 'string',
            'status' => 'string'
        ]);
        $condition['trace_id'] = $request->input('trace_id', null);
        $condition['admin_user_id'] = $request->input('admin_user_id', null);
        $condition['ip'] = $request->input('ip', null);
        $condition['path'] = $request->input('path', null);
        $condition['status'] = $request->input('status', null);
        $list = $this->logOperationService->listPaginateByCondition($condition)->appends($request->all());
        $adminUser = $this->adminUserService->listAllAdminUserName();
        $operationStatus = LogOperationStatusEnum::getAllConst();
        return view('_system.log.operation')
            ->with('request', $request)
            ->with('adminUser', $adminUser)
            ->with('operationStatus', $operationStatus)
            ->with('list', $list);
    }

}