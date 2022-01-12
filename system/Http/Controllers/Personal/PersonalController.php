<?php

namespace System\Http\Controllers\Personal;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;
use System\Exceptions\PermissionDenyException;
use System\Services\Admin\AdminUserService;
use System\Services\Personal\PersonalService;

class PersonalController extends BaseController
{
    /**
     * @var PersonalService
     */
    protected $personalService;

    /**
     * @var AdminUserService
     */
    protected $adminUserService;

    /**
     * PersonalController constructor.
     * @param PersonalService $personalService
     * @param AdminUserService $adminUserService
     */
    public function __construct(PersonalService $personalService, AdminUserService $adminUserService)
    {
        $this->personalService = $personalService;
        $this->adminUserService = $adminUserService;
    }

    /**
     * 修改密码页
     *
     * @return \Illuminate\View\View
     */
    public function passwordIndex()
    {
        return view('_personal.password');
    }

    /**
     * 新密码提交
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws PermissionDenyException
     */
    public function passwordSubmit(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required|string',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|string',
        ]);
        $oldPassword = $request->input('old_password');
        $password = $request->input('password');
        $this->personalService->resetPassword($oldPassword, $password);
        session()->flush();
        return ajax_response()->ajaxSuccessResponse('修改成功，请重新登录', [], 'redirect', url('login'));
    }

    /**
     * 个人信息详情页
     *
     * @return view
     */
    public function infoIndex()
    {
        $adminUserId = session()->get('admin_user_id');
        $adminUser = $this->adminUserService->getByAdminUserId($adminUserId);
        return view('_personal.info')
            ->with('adminUser', $adminUser);
    }

    /**
     * 提交个人信息
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function infoSubmit(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'mobile' => 'digits:11',
            'mail' => 'email',
        ]);
        $params['name'] = $request->input('name');
        $params['mobile'] = $request->input('mobile');
        if (empty($params['mobile'])) {
            $params['mobile'] = null;
        }
        $params['mail'] = $request->input('mail');
        if (empty($params['mail'])) {
            $params['mail'] = null;
        }
        $this->personalService->submitInfo($params);
        return ajax_response()->ajaxSuccessResponse('个人信息修改成功', [], 'reload');
    }
}