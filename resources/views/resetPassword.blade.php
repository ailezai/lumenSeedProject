<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>重设密码</title>
    <link rel="stylesheet" href="{!! auto_asset('css/vendor.css') !!}" />
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/toastr/toastr.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/sweetalert/sweetalert.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/app.css') !!}" />
</head>

<body class="gray-bg">

<div class="passwordBox animated fadeInDown">
    <div class="row">

        <div class="col-md-12">
            <div class="ibox-content">

                <h2 class="font-bold">重设密码</h2>

                <p>
                    请重新输入您的密码
                </p>

                <div class="row">

                    <div class="col-lg-12">
                        <form class="m-t" role="form" action="{{ auto_url('/forget') }}" method="post">
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="form-group">
                                <input type="password" name="password" class="form-control" placeholder="请输入新密码" required="" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <input type="password" name="password_confirmation" class="form-control" placeholder="请再次输入新密码" required="" autocomplete="off">
                            </div>
                            {{--<input type="hidden" name="_token" value="{{ csrf_token() }}" />--}}
                            <button type="submit" class="btn btn-primary block full-width m-b">提交新密码</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>