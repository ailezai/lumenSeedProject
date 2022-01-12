<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录</title>
    <link rel="stylesheet" href="{!! auto_asset('css/vendor.css') !!}" />
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/toastr/toastr.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/sweetalert/sweetalert.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/app.css') !!}" />
</head>

<body class="gray-bg" style="padding-top: 50px;">
<h1 class="logo-name text-center nomargin" style="font-size: 80px;">{{ config('webConfig.view.login.logo') }}</h1>
<div class="middle-box text-center loginscreen animated fadeInDown">
    <div>
        <h3>{{ config('webConfig.view.login.title') }}</h3>
        <form action="{{ auto_url('login') }}" method="post"
              data-ajax-form="true" class="m-t" id="login-form">
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="用户名" autocomplete="off" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="密码" autocomplete="off" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="auth_code" placeholder="授权码，若无则无需填写">
            </div>
            <div class="form-group clearfix">
                <div class="col-xs-6 no-padding">
                    <input class="form-control" id="captcha_code" placeholder="验证码" name="captcha_code" size="50" type="text" value="" autocomplete="off">
                </div>
                <div class="col-xs-6 no-padding">
                    <a href="javascript:$.admin.reloadCaptchaCode();" title="更换一张验证码图片">
                        <img src="{{ auto_url('vCode?_=') }}{{ microtime(true) }}" id="captcha_code_img">
                    </a>
                    <a href="javascript:$.admin.reloadCaptchaCode();">换一张</a>
                </div>
            </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <button type="submit" class="btn btn-primary block full-width m-b" data-id="login-form">登录</button>
            {{--<a href="{{ curl('/forget') }}"><small>忘记密码？</small></a>--}}
        </form>
    </div>
</div>
<script src="{!! auto_asset('js/app.js') !!}"></script>
<script src="{!! auto_asset('js/plugins/toastr/toastr.min.js') !!}"></script>
<script src="{!! auto_asset('js/plugins/sweetalert/sweetalert.min.js') !!}"></script>
<script src="{!! auto_asset('js/global.js') !!}"></script>
@include('layouts.preAction')
</body>
</html>