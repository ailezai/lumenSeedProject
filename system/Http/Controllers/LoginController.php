<?php

namespace System\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use AiLeZai\Common\Lib\Captcha\Captcha;
use AiLeZai\Common\Lib\Common\IpUtil;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;
use Laravel\Lumen\Http\Redirector;
use System\Exceptions\LoginException;
use System\Exceptions\PermissionDenyException;
use System\Services\LoginService;

class LoginController extends BaseController
{
    /**
     * @var LoginService
     */
    protected $loginService;

    /**
     * LoginController constructor.
     * @param LoginService $loginService
     */
    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    /**
     * 登录页校验
     *
     * @return View
     */
    public function loginPage()
    {
        if (!empty(session()->get('admin_user_id'))) {
            return redirect('index');
        }
        return view('login');
    }

    /**
     * 验证码
     *
     * @param Captcha $captcha
     *
     * @return mixed
     */
    public function vCode(Captcha $captcha)
    {
        header("Content-type:image/png");

        //设置验证码高度
        $captcha->setHeight(34);

        //设置字符个数
        $captcha->setTextNumber(4);

        //设置背景颜色
        $captcha->setBgColor('#FFFFFF');

        //设置干扰点数量
        $captcha->setNoisePoint(50);

        //设置干扰线数量
        $captcha->setNoiseLine(0);

        //输出验证码
        $code  = $captcha->createImage();
        $vCode = $code . '|' . time();

        return session()->put('vcode', $vCode);
    }

    /**
     * 登录
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws LoginException
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required|string',
            'auth_code' => 'string',
            'captcha_code' => 'required|string'
        ]);
        $username = strtolower($request->input('username'));
        $password = $request->input('password');
        $captchaCode = $request->input('captcha_code');
        $authCode = $request->input('auth_code');
        $ip = ip2long(IpUtil::getCurrentIP());
        $this->loginService->login($username, $password, $captchaCode, $authCode, $ip);
        return ajax_response()->ajaxSuccessResponse('登录成功', [], 'redirect', url('/index'));
    }

    /**
     * 退出
     *
     * @return RedirectResponse
     */
    public function logout()
    {
        if (empty(session()->get('admin_user_id'))) {
            session()->flush();
            return redirect('login');
        }

        session()->flush();
        $preAction = [
            'method' => 'notify',
            'message' => '退出成功',
            'type' => 'success'
        ];
        session()->put('__preAction', $preAction);
        return redirect('login');
    }

    /**
     * 忘记密码页校验
     *
     * @return View
     */
    public function forgetPasswordPage()
    {
//        session()->forget('admin_user_id');
        session()->flush();
        return view('forgetPassword');
    }

    /**
     * 发送重置密码请求 TODO
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forgetPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);
//        return ajax_response()->ajaxSuccessResponse('密码重置成功，请使用邮件中的密码重新登录', [], 'redirect', url('/login'));
        return ajax_response()->ajaxFailureResponse('该功能暂未启用，请联系管理员');
    }

    /**
     * 重置密码页 TODO
     *
     * @param Request $request
     *
     * @return View
     */
    public function resetPasswordPage(Request $request)
    {
//        session()->forget('admin_user_id');
        session()->flush();
        return view('resetPassword')
            ->with('token', $request->input('token', ''));
    }

    /**
     * 提交重置密码 TODO
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);
        return ajax_response()->ajaxFailureResponse('该功能暂未启用，请联系管理员');
    }

    /**
     * 切换管理员
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws DataNotFoundException
     * @throws PermissionDenyException
     */
    public function switchAdminUser(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string'
        ]);
        $username = strtolower($request->input('username'));
        $this->loginService->switchAdminUser($username);
        return ajax_response()->ajaxSuccessResponse('切换成功', [], 'redirect', url('index'));
    }

    /**
     * 使用Jwt登录
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws DataNotFoundException
     * @throws PermissionDenyException
     */
    public function jwtLogin(Request $request)
    {
        $this->validate($request, [
            'jwt_token' => 'required|string'
        ]);
        $jwt = $request->input('jwt_token', '');
        $this->loginService->loginWithJwtToken($jwt);
        return redirect('index');
    }

    /**
     * 使用jwt登录其他站点
     *
     * @param Request $request
     *
     * @return RedirectResponse|Redirector
     */
    public function jwtRedirect(Request $request)
    {
        $this->validate($request, [
            'audience' => 'required|string'
        ]);
        $audience = $request->input('audience');

        // 生成
        $jwt = $this->loginService->generateLoginJwtToken($audience);
        $redirectUrl = env('AUDIENCE_'.$audience) . '/jwt_login?jwt_token=' . $jwt;
        return redirect($redirectUrl);
    }
}