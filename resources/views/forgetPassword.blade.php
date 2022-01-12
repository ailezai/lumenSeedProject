<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>忘记密码</title>
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

                <h2 class="font-bold">忘记密码</h2>

                <p>
                    输入邮箱地址，我们将向提供的注册邮箱发送邮件您的新密码
                </p>

                <div class="row">

                    <div class="col-lg-12">
                        <form class="m-t" role="form" action="{{ auto_url('/forget') }}" method="post">
                            <div class="form-group">
                                <input type="email" name="email" class="form-control" placeholder="请输入邮箱地址" required="" autocomplete="off">
                            </div>
                            {{--<input type="hidden" name="_token" value="{{ csrf_token() }}" />--}}
                            <button type="submit" class="btn btn-primary block full-width m-b">重置密码</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>