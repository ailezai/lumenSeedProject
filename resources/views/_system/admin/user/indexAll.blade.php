@extends('layouts.app')

@section('title', '所有管理员')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
@stop

@inject('presenter', 'System\Presenters\Admin\AdminUserPresenter')
@section('content')
    {{-- 操作&搜索&数据 --}}
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        {{-- 操作 --}}
        @if(session()->get('admin_user_id') == 1)
            <div class="ibox-content m-b-sm border-bottom">
                <a href="{{ auto_url('system/user/index') }}" class="btn btn-w-m btn-info" >查看可管理管理员</a>
                <a href="{{ auto_url('system/user/refresh_all') }}" class="btn btn-w-m btn-primary" data-ajax-get="true">刷新所有权限</a>
            </div>
        @endif
        {{-- 操作 --}}

        {{-- 搜索 --}}
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="ibox">
                    <div class="ibox-title collapse-link">
                        <h5>搜索</h5>
                        <div class="ibox-tools">
                            <a><i class="fa fa-chevron-up"></i></a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ auto_url($request->path()) }}" id="list-search-form" method="get">
                            <div class="row">
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-username">登录名</label>
                                        <input class="form-control" id="search-username" name="username" value="{{ $request->input('username', '') }}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-name">姓名（支持模糊搜索）</label>
                                        <input class="form-control" id="search-name" name="name" value="{{ $request->input('name', '') }}" autocomplete="off">
                                    </div>
                                </div>

                                <input type="hidden" name="all" value="1">

                                {{-- 搜索&清空 --}}
                                <div class="col-md-3 col-lg-3 pull-right">
                                    <div class="form-group pull-right">
                                        <label class="font-bold"></label>
                                        <div>
                                            <button type="submit" class="btn btn-w-m btn-primary">搜索</button>&nbsp;
                                            <button type="reset" class="btn btn-w-m btn-default">清空</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- /搜索&清空 --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- 搜索 --}}

        {{-- 数据 --}}
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        {{-- table --}}
                        <table class="footable table table-stripped toggle-arrow-tiny">
                            <thead>
                            <tr>
                                <th>用户名</th>
                                <th>姓名</th>
                                <th>邮箱</th>
                                <th>手机</th>
                                <th>最后登录IP</th>
                                <th>最后登录时间</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($list) == 0)
                                <tr><td colspan="100" style="text-align:center">暂无数据</td></tr>
                            @else
                                @foreach($list as $item)
                                    <tr>
                                        <td>{{ $item->username }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->mail ?? '--'}}</td>
                                        <td>{{ $item->mobile ?? '--' }}</td>
                                        <td>{{ $item->login_ip ? long2ip($item->login_ip) : '--' }}</td>
                                        <td>{{ $item->login_time ?? '--' }}</td>
                                        <td>{!! $presenter->formatStatus($item->status, $item->admin_user_id) !!}</td>
                                        <td>
                                            <a class="btn btn-xs btn-primary" href="{{ auto_url('system/user/detail?admin_user_id='.$item->admin_user_id) }}"><i class="fa fa-file-text"></i></a>
                                            <a class="btn btn-xs btn-warning" href="{{ auto_url('system/user/permission_page?admin_user_id='.$item->admin_user_id) }}">独立授权</a>
                                            <a class="btn btn-xs btn-danger" data-href="{{ auto_url('system/user/reset_page?user_id='.$item->admin_user_id) }}" data-pjax-request="true">重置密码</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                            <tfoot>
                            @if(is_object($list)
                                && ($list instanceof \Illuminate\Pagination\Paginator
                                    || $list instanceof \Illuminate\Pagination\LengthAwarePaginator))
                                <tr>
                                    <td colspan="20">
                                        <ul class="pull-right">
                                            每页{{ $list->perPage() }}条，当前页{{ $list->count() }}条，共{{ $list->total() }}条
                                            {{ $list }}
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- 数据 --}}
    </div>
    {{-- 操作&搜索&数据 --}}
@stop

@section('scripts')
    {{-- Jquery Validate --}}
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.min.js') !!}"></script>
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.zh-CN.min.js') !!}"></script>
    {{-- FooTable --}}
    <script src="{!! auto_asset('js/plugins/footable/footable.all.min.js') !!}"></script>

    <script>
        $(function () {
            $("#list-search-form").validate({
                rules: {

                }
            });

            // footable配置
            $('.footable').footable({
                "paginate": false,
                "sort": false
            });
        });
    </script>
@stop