<?php


namespace System\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use ReflectionException;
use System\Enums\Admin\AdminRoleTypeEnum;
use System\Exceptions\PermissionDenyException;
use System\Services\Admin\AdminRoleService;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;

class AdminRoleController extends BaseController
{
    /**
     * @var AdminRoleService
     */
    protected $adminRoleService;

    /**
     * AdminRoleController constructor.
     *
     * @param AdminRoleService $adminRoleService
     */
    public function __construct(AdminRoleService $adminRoleService)
    {
        $this->adminRoleService = $adminRoleService;
    }

    /**
     * 角色列表页
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws PermissionDenyException
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'all' => 'boolean'
        ]);

        $condition['all'] = $request->input('all', 0);
        if ($condition['all']) {
            if (session()->get('admin_user_id') != 1) {
                throw new PermissionDenyException('权限不足');
            }
            $view = view('_system.admin.role.indexAll');
            $list = $this->adminRoleService->listPaginateByCondition($condition)->appends($request->all());
        } else {
            $view = view('_system.admin.role.index');
            $list = $this->adminRoleService->listAllByCondition($condition);

        }

        return $view
            ->with('request', $request)
            ->with('list', $list);
    }

    /**
     * 添加角色页
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws ReflectionException
     */
    public function addPage(Request $request)
    {
        $this->validate($request, [
            'role_id' => 'required|numeric'
        ]);
        $parentRoleId = $request->input('role_id');
        $parentRole = $this->adminRoleService->getByRoleId($parentRoleId);

        return view('_system.admin.role.addPage')
            ->with('parentRoleId', $parentRoleId)
            ->with('type', AdminRoleTypeEnum::getAllConst())
            ->with('parentRole', $parentRole);
    }

    /**
     * 编辑角色页
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws DataNotFoundException
     * @throws ReflectionException
     */
    public function editPage(Request $request)
    {
        $this->validate($request, [
            'role_id' => 'required|numeric'
        ]);
        $roleId = $request->input('role_id');
        $role = $this->adminRoleService->getByRoleId($roleId);
        if (empty($role)) {
            throw new DataNotFoundException('角色不存在');
        }
        $parentRole = $this->adminRoleService->getByRoleId($role->parent_role_id);
        return view('_system.admin.role.editPage')
            ->with('request', $request)
            ->with('type', AdminRoleTypeEnum::getAllConst())
            ->with('parentRole', $parentRole)
            ->with('role', $role);
    }

    /**
     * 新增角色
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws DataNotFoundException
     * @throws PermissionDenyException
     */
    public function addSubmit(Request $request)
    {
        $this->validate($request, [
            'parent_role_id' => 'required|numeric',
            'alias' => 'required|string',
            'name' => 'required|string',
            'type' => 'required|string',
            'group' => 'required|string',
            'permission' => 'array',
        ]);
        $params['parentRoleId'] = $request->input('parent_role_id');
        $params['alias'] = $request->input('alias');
        $params['name'] = $request->input('name');
        $params['type'] = $request->input('type');
        $params['group'] = $request->input('group');
        $params['permission'] = $request->input('permission', []);

        $this->adminRoleService->addSubmit($params);
        return ajax_response()->ajaxSuccessResponse('提交成功', [], 'redirect', url('system/role/index'));
    }

    /**
     * 修改角色
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws DataNotFoundException
     * @throws PermissionDenyException
     */
    public function editSubmit(Request $request)
    {
        $this->validate($request, [
            'role_id' => 'required|numeric',
            'parent_role_id' => 'required|numeric',
            'alias' => 'required|string',
            'name' => 'required|string',
            'type' => 'required|string',
            'group' => 'required|string',
            'permission' => 'array',
        ]);
        $roleId = $request->input('role_id', null);
        $params['parentRoleId'] = $request->input('parent_role_id');
        $params['alias'] = $request->input('alias');
        $params['name'] = $request->input('name');
        $params['type'] = $request->input('type');
        $params['group'] = $request->input('group');
        $params['permission'] = $request->input('permission', []);

        $this->adminRoleService->editSubmit($roleId, $params);
        return ajax_response()->ajaxSuccessResponse('提交成功', [], 'redirect', url('system/role/index'));
    }

    /**
     * 删除角色
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
            'role_id' => 'required|numeric'
        ]);
        $roleId = $request->input('role_id');
        $this->adminRoleService->delete($roleId);
        return ajax_response()->ajaxSuccessResponse('删除成功', [], 'reload');
    }
}