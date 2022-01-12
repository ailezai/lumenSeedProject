<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404</title>
    <link rel="stylesheet" href="{!! auto_asset('css/vendor.css') !!}" />
    <link rel="stylesheet" href="{!! auto_asset('css/app.css') !!}" />
</head>

<body class="gray-bg">

<div class="middle-box text-center animated fadeInDown">
    <h1>404</h1>
    <h3 class="font-bold">抱歉，页面好像去火星了~</h3>

    <div class="error-desc">
        <a href="javascript:history.back(-1);" class="btn btn-default m-t">返回上一页</a>
        <a href="" class="btn btn-primary m-t">首页</a>
    </div>
    @if(env('APP_DEBUG') === true)<div class="hide">{{ $msg }}</div>@endif
</div>
<script src="{!! auto_asset('js/app.js') !!}"></script>
</body>
</html>