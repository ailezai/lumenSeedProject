<?php

namespace System\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use System\Exceptions\PermissionDenyException;
use System\Services\Admin\AdminPermissionService;
use System\Services\Admin\AdminRoleService;
use System\Services\Admin\AdminUserService;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;
use System\Supports\HotRefresh;

class AdminUserController extends BaseController
{
    /**
     * @var AdminUserService
     */
    protected $adminUserService;

    /**
     * @var AdminRoleService
     */
    protected $adminRoleService;

    /**
     * @var AdminPermissionService
     */
    protected $adminPermissionService;

    /**
     * AdminUserController constructor.
     * @param AdminUserService $adminUserService
     * @param AdminRoleService $adminRoleService
     * @param AdminPermissionService $adminPermissionService
     */
    public function __construct(AdminUserService $adminUserService, AdminRoleService $adminRoleService, AdminPermissionService $adminPermissionService)
    {
        $this->adminUserService = $adminUserService;
        $this->adminRoleService = $adminRoleService;
        $this->adminPermissionService = $adminPermissionService;
    }

    /**
     * 管理员页面
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
        $condition['username'] = $request->input('username', null);
        $condition['name'] = $request->input('name', null);
        if ($condition['all']) {
            if (session()->get('admin_user_id') != 1) {
                throw new PermissionDenyException('权限不足');
            }
            $view = view('_system.admin.user.indexAll');
            $list = $this->adminUserService->listPaginateByCondition($condition)->appends($request->all());
        } else {
            $view = view('_system.admin.user.index');
            $list = $this->adminUserService->listPaginateByConditionInManage($condition)->appends($request->all());
        }

        return $view
            ->with('request', $request)
            ->with('list', $list);
    }

    /**
     * 管理员添加页
     *
     * @return View
     */
    public function addPage()
    {
        $roles = $this->adminRoleService->listAllByCondition();
        return view('_system.admin.user.addPage')
            ->with('roles', $roles);
    }

    /**
     * 管理员编辑页
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws DataNotFoundException
     */
    public function editPage(Request $request)
    {
        $this->validate($request, [
            'admin_user_id' => 'required|numeric'
        ]);
        $roles = $this->adminRoleService->listAllByCondition();
        $adminUserId = $request->input('admin_user_id');
        $adminUser = $this->adminUserService->getByAdminUserId($adminUserId);
        if (empty($adminUser)) {
            throw new DataNotFoundException('管理员不存在');
        }

        $adminUserRole = array_column($adminUser->role_grant->toArray(), 'role_id');
        $adminUserRoleAdmin = [];
        foreach ($adminUser->role_grant->toArray() as $item) {
            if ($item['pivot']['is_admin'] == 1) {
                $adminUserRoleAdmin[] = $item['role_id'];
            }
        }

        return view('_system.admin.user.editPage')
            ->with('roles', $roles)
            ->with('adminUser', $adminUser)
            ->with('adminUserRole', $adminUserRole)
            ->with('adminUserRoleAdmin', $adminUserRoleAdmin);
    }

    /**
     * 新增管理员
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
            'username' => 'required|string',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required|string',
            'name' => 'required|string',
            'mobile' => 'digits:11',
            'mail' => 'email',
            'role' => 'required|array',
            'role_admin' => 'array',
        ]);
        $params['username'] = strtolower($request->input('username'));
        $params['password'] = $request->input('password');
        $params['name'] = $request->input('name');
        $params['mobile'] = $request->input('mobile');
        $params['mail'] = $request->input('mail');
        $params['role'] = $request->input('role');
        $params['role_admin'] = $request->input('role_admin');
        $this->adminUserService->addSubmit($params);
        return ajax_response()->ajaxSuccessResponse('添加成功', [], 'redirect', url('system/user/index'));
    }

    /**
     * 编辑管理员
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
            'admin_user_id' => 'required|numeric',
            'username' => 'required|string',
            'name' => 'required|string',
            'mobile' => 'digits:11',
            'mail' => 'email',
            'role' => 'required|array',
            'role_admin' => 'array',
        ]);
        $userId = $request->input('admin_user_id', null);
        $params['username'] = $request->input('username');
        $params['name'] = $request->input('name');
        $params['mobile'] = $request->input('mobile');
        $params['mail'] = $request->input('mail');
        $params['role'] = $request->input('role');
        $params['role_admin'] = $request->input('role_admin');
        $this->adminUserService->editSubmit($userId, $params);
        return ajax_response()->ajaxSuccessResponse('修改成功', [], 'redirect', url('system/user/index'));
    }

    /**
     * 修改管理员状态
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws PermissionDenyException
     */
    public function status(Request $request)
    {
        $this->validate($request, [
            'admin_user_id' => 'required|numeric',
            'status' => 'required|string'
        ]);
        $adminUserId = $request->input('admin_user_id');
        $status = $request->input('status');
        $this->adminUserService->updateStatusByUserId($adminUserId, $status);
        return ajax_response()->ajaxSuccessResponse('状态修改成功', [], 'reload');
    }

    /**
     * 管理员详情
     *
     * @param Request $request
     *
     * @return $this
     */
    public function detail(Request $request)
    {
        $this->validate($request, [
            'admin_user_id' => 'required|numeric'
        ]);
        $adminUserId = $request->input('admin_user_id');
        $adminUser = $this->adminUserService->getByAdminUserId($adminUserId);
        return view('_system.admin.user.detail')
            ->with('adminUser', $adminUser);
    }

    /**
     * 重置密码页
     *
     * @param Request $request
     *
     * @return $this
     */
    public function resetPage(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|numeric'
        ]);
        $adminUserId = $request->input('user_id');
        $this->adminUserService->getByAdminUserId($adminUserId);
        return view('_system.admin.user.reset')
            ->with('adminUserId', $adminUserId);
    }

    /**
     * 重置密码
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws PermissionDenyException
     */
    public function resetSubmit(Request $request)
    {
        $this->validate($request, [
            'admin_user_id' => 'required|numeric',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|string',
        ]);
        $adminUserId = $request->input('admin_user_id');
        $password = $request->input('password');
        $this->adminUserService->resetPassword($adminUserId, $password);
        return ajax_response()->ajaxSuccessResponse('修改成功，请重新登录', [], 'reload');
    }

    /**
     * 刷新所有用户权限
     *
     * @return JsonResponse
     *
     * @throws PermissionDenyException
     */
    public function refreshAll()
    {
        if (session()->get('admin_user_id') != 1) {
            throw new PermissionDenyException('权限不足');
        }
        HotRefresh::refreshAll();
        return ajax_response()->ajaxSuccessResponse('所有用户权限刷新成功', [], 'reload');
    }

    /**
     * 独立授权页
     *
     * @param Request $request
     *
     * @return $this
     *
     * @throws PermissionDenyException
     */
    public function permissionPage(Request $request)
    {
        if (session()->get('admin_user_id') != 1) {
            throw new PermissionDenyException('权限不足');
        }

        $this->validate($request, [
            'admin_user_id' => 'required|numeric'
        ]);
        $adminUserId = $request->input('admin_user_id');
        $adminUser = $this->adminUserService->getByAdminUserId($adminUserId);
        $permission = $this->adminPermissionService->listAllPermission();
        return view('_system.admin.user.permissionPage')
            ->with('adminUser', $adminUser)
            ->with('permission', $permission);
    }

    /**
     * 提交独立授权信息
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws PermissionDenyException
     * @throws DataNotFoundException
     */
    public function permissionSubmit(Request $request)
    {
        if (session()->get('admin_user_id') != 1) {
            throw new PermissionDenyException('权限不足');
        }
        $this->validate($request, [
            'admin_user_id' => 'required|numeric',
            'grant' => 'array',
            'forbid' => 'array'
        ]);
        $adminUserId = $request->input('admin_user_id');
        $grant = $request->input('grant', []);
        $forbid = $request->input('forbid', []);
        $this->adminUserService->permissionSubmit($adminUserId, $grant, $forbid);
        return ajax_response()->ajaxSuccessResponse('独立授权完成', [], 'back');

    }
}