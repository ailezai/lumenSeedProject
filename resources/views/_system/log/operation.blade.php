@extends('layouts.app')

@section('title', '操作日志')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/select2/select2.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
@stop

@inject('presenter', 'System\Presenters\Log\LogOperationPresenter')
@section('content')
    {{-- 操作&搜索&数据 --}}
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">

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
                                        <label class="font-bold" for="search-trace-id">Trace-id</label>
                                        <input class="form-control" id="search-trace-id" name="trace_id" value="{{ $request->input('trace_id', '') }}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-admin-user-id">管理员</label>
                                        <select class="form-control" id="search-admin-user-id" name="admin_user_id">
                                            <option value="" @if($request->input('admin_user_id', '') === '') selected @endif>所有</option>
                                            <option value="0" @if($request->input('admin_user_id', '') === 0) selected @endif>未知管理员</option>
                                            @foreach($adminUser as $item)
                                                <option value="{{ $item->admin_user_id }}" @if($request->input('admin_user_id', '') == $item->admin_user_id) selected @endif>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-ip">IP地址</label>
                                        <input class="form-control" id="search-ip" name="ip" value="{{ $request->input('ip', '') }}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-path">路由</label>
                                        <input class="form-control" id="search-path" name="path" value="{{ $request->input('path', '') }}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-status">状态</label>
                                        <select class="form-control" id="search-status" name="status">
                                            <option value="" @if($request->input('status', '') === '') selected @endif>所有</option>
                                            @foreach($operationStatus as $item)
                                                <option value="{{ $item[0] }}" @if($request->input('status', '') == $item[0]) selected @endif>{{ $item[1] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- 搜索&清空 --}}
                                <div class="col-md-3 col-lg-3 view-page-search-form pull-right">
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
                                <th>ID</th>
                                <th>Trace-id</th>
                                <th data-toggle="true">操作者</th>
                                <th>IP地址</th>
                                <th>SESSION(会话)</th>
                                <th>根路径</th>
                                <th>方法</th>
                                <th>路由</th>
                                <th data-hide="all">参数</th>
                                <th data-hide="all">错误信息</th>
                                <th>状态</th>
                                <th>操作时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($list) == 0)
                                <tr><td colspan="100" style="text-align:center">暂无数据</td></tr>
                            @else
                                @foreach($list as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->trace_id }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ long2ip($item->ip) }}</td>
                                        <td>{{ $item->session }}</td>
                                        <td>{{ $item->host }}</td>
                                        <td>{{ $item->method }}</td>
                                        <td>{{ $item->path }}</td>
                                        <td>
                                            <pre>{{ json_encode(json_decode($item->request), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </td>
                                        <td>{{ $item->error_message }}</td>
                                        <td>{!! $presenter->formatStatus($item->status) !!}</td>
                                        <td>{{ $item->create_time }}</td>
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
    </div>
    {{-- 数据 --}}
@stop

@section('scripts')
    {{-- Jquery Validate --}}
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.min.js') !!}"></script>
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.zh-CN.min.js') !!}"></script>
    {{-- FooTable --}}
    <script src="{!! auto_asset('js/plugins/footable/footable.all.min.js') !!}"></script>
    {{-- Select2 --}}
    <script src="{!! auto_asset('js/plugins/select2/select2.full.min.js') !!}"></script>

    <script>
        $(function () {
            $("#list-search-form").validate({
                rules: {
                    admin_user_id: {
                        number: true
                    }
                }
            });

            // select2
            $("#search-admin-user-id").select2();
            $("#search-status").select2();

            // footable配置
            $('.footable').footable({
                "paginate": false,
                "sort": false
            });
        });
    </script>
@stop