<?php

namespace System\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use ReflectionException;
use System\Enums\Admin\RequestMethodEnum;
use System\Exceptions\PermissionDenyException;
use System\Services\Admin\AdminPermissionService;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;

class AdminPermissionController extends BaseController
{
    /**
     * @var AdminPermissionService
     */
    protected $adminPermissionService;

    /**
     * AdminPermissionController constructor.
     *
     * @param AdminPermissionService $adminPermissionService
     */
    public function __construct(AdminPermissionService $adminPermissionService)
    {
        $this->adminPermissionService = $adminPermissionService;
    }

    /**
     * 权限列表
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request)
    {
        $list = $this->adminPermissionService->listPaginateByCondition()->appends($request->all());
        return view('_system.admin.permission.index')
            ->with('request', $request)
            ->with('list', $list);
    }

    /**
     * 权限添加页
     *
     * @return View
     *
     * @throws ReflectionException
     */
    public function addPage()
    {
        return view('_system.admin.permission.addPage')
            ->with('requestMethod', RequestMethodEnum::getAllConst());
    }

    /**
     * 权限编辑页
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws DataNotFoundException
     *
     * @throws ReflectionException
     */
    public function editPage(Request $request)
    {
        $this->validate($request, [
            'permission_id' => 'required|numeric'
        ]);
        $permissionId = $request->input('permission_id');
        $permission = $this->adminPermissionService->getByPermissionId($permissionId);
        if (empty($permission)) {
            throw new DataNotFoundException('权限不存在');
        }
        return view('_system.admin.permission.editPage')
            ->with('requestMethod', RequestMethodEnum::getAllConst())
            ->with('permission', $permission);
    }

    /**
     * 新增权限
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws PermissionDenyException
     */
    public function addSubmit(Request $request)
    {
        $this->validate($request, [
            'alias' => 'required|string',
            'name' => 'required|string',
            'methods' => 'required|string',
            'path' => 'required|string',
        ]);
        $params['alias'] = $request->input('alias');
        $params['name'] = $request->input('name');
        $params['method'] = $request->input('methods');
        $params['path'] = $request->input('path');
        $this->adminPermissionService->addSubmit($params);
        return ajax_response()->ajaxSuccessResponse('提交成功', [], 'reload');
    }

    /**
     * 修改权限
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws PermissionDenyException
     */
    public function editSubmit(Request $request)
    {
        $this->validate($request, [
            'permission_id' => 'required|numeric',
            'alias' => 'required|string',
            'name' => 'required|string',
            'methods' => 'required|string',
            'path' => 'required|string',
        ]);
        $permissionId = $request->input('permission_id');
        $params['alias'] = $request->input('alias');
        $params['name'] = $request->input('name');
        $params['method'] = $request->input('methods');
        $params['path'] = $request->input('path');
        $this->adminPermissionService->editSubmit($permissionId, $params);
        return ajax_response()->ajaxSuccessResponse('提交成功', [], 'reload');
    }

    /**
     * 删除权限
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws PermissionDenyException
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'permission_id' => 'required|numeric'
        ]);
        $permissionId = $request->input('permission_id');
        $this->adminPermissionService->delete($permissionId);
        return ajax_response()->ajaxSuccessResponse('删除成功', [], 'reload');
    }
}