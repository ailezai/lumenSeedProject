<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('webConfig.view.title') }} - @yield('title') </title>
    @section('linksheader')
    @show
    <link rel="stylesheet" href="{!! auto_asset('css/vendor.css') !!}" />
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/toastr/toastr.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/sweetalert/sweetalert.css') !!}">
    @section('linksmiddle')
    @show
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/iCheck/custom.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/app.css') !!}" />
    @section('linksbottom')
    @show
    <style type="text/css">
        #left-navigation-search {
            border: none;
            border-bottom: 1px solid transparent;
            background: none;
            height: 50px;
            padding-left: 30px;
            width: 100%;
        }

        .view-page-search-form {
            height: 72px;
        }
    </style>
</head>
<body class="fixed-sidebar">

{{-- Wrapper --}}
<div id="wrapper">
    {{-- Navigation --}}
    @include('layouts.navigation')
    {{-- Page wraper --}}
    <div id="page-wrapper" class="gray-bg">
        {{-- Topnavbar --}}
        @include('layouts.topnavbar')
        {{-- Bread crumb --}}
        @include('layouts.breadcrumb')
        {{-- Main view --}}
        @yield('content')
        {{-- Footer --}}
        @include('layouts.footer')
    </div>
    {{-- End page wrapper --}}
</div>
{{-- End wrapper --}}

{{-- Modal Pjax --}}
{{--
    <a data-href="{{ auto_url('') }}" data-pjax-request="true">xxx</a>
--}}
<div class="modal inmodal fade" id="modal-pjax" role="dialog" aria-hidden="true">
    <style>
        /*select2在Bootstrap的modal中默认被遮盖，现在强制显示在最前*/
        .select2-container--open{
            z-index:9999999;
            /*position: relative !important;*/
        }
    </style>
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modal-pjax-content">

        </div>
    </div>
</div>

<script src="{!! auto_asset('js/app.js') !!}" type="text/javascript"></script>
<script src="{!! auto_asset('js/plugins/toastr/toastr.min.js') !!}"></script>
<script src="{!! auto_asset('js/plugins/sweetalert/sweetalert.min.js') !!}"></script>
<script src="{!! auto_asset('js/plugins/iCheck/icheck.min.js')  !!}"></script>
<script src="{!! auto_asset('js/plugins/quicksearch/jquery.quicksearch.js')  !!}"></script>
@section('scripts')
@show
<script src="{!! auto_asset('js/global.js') !!}"></script>
<script>
    $('input#left-navigation-search').quicksearch('li[data-type="left-navigation"]');
</script>
@if(session()->get('system_user') == 1)
    <script>
        $("#system-switch-admin-username").change(function() {
            var username = $(this).val();
            swal({
                    title: '',
                    text: '确认切换账号？',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#DD6B55',
                    cancelButtonText: '取消',
                    confirmButtonText: '确定'
                },
                function() {
                    $.ajax({
                        url: '{{ auto_url('system/switch_user?username=') }}' + username,
                        type: 'get',
                        dataType: 'json',
                        success: function (json) {
                            $.admin.ajaxCallback(json)
                        }
                    });
                });
        });
    </script>
@endif
@include('layouts.preAction')
</body>
</html>