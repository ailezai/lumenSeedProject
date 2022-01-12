@extends('layouts.app')

@section('title', '权限')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
@stop

@inject('presenter', 'System\Presenters\Admin\AdminPermissionPresenter')
@section('content')
    {{-- 操作&搜索&数据 --}}
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        {{-- 操作 --}}
        <div class="ibox-content m-b-sm border-bottom">
            <a data-href="{{ auto_url('system/permission/add_page') }}" class="btn btn-w-m btn-info" data-pjax-request="true">新增权限</a>
        </div>
        {{-- 操作 --}}

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
                                <th>标识</th>
                                <th>权限名称</th>
                                <th>请求</th>
                                <th>最后修改时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($list) == 0)
                                <tr><td colspan="100" style="text-align:center">暂无数据</td></tr>
                            @else
                                @foreach($list as $item)
                                    <tr>
                                        <td>{{ $item->permission_id }}</td>
                                        <td>{{ $item->alias }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{!! $presenter->formatRequest($item->method, $item->path) !!}</td>
                                        <td>{{ $item->modify_time }}</td>
                                        <td>
                                            <a class="btn btn-xs btn-warning" data-href="{{ auto_url('system/permission/edit_page?permission_id='.$item->permission_id) }}" data-pjax-request="true"><i class="fa fa-pencil"></i></a>
                                            @if(session()->get('admin_user_id') == 1)
                                                <a class="btn btn-xs btn-danger" onclick="$.admin.tipBeforeAjax('{{ auto_url('system/permission/delete?permission_id='.$item->permission_id) }}', '删除权限', '确认删除权限？该操作不可撤销', 'error')"><i class="fa fa-trash"></i></a>
                                            @endif
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