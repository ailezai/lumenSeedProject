@extends('layouts.app')

@section('title', '角色')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
@stop

@inject('presenter', 'System\Presenters\Admin\AdminRolePresenter')
@section('content')
    {{-- 操作&搜索&数据 --}}
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        {{-- 操作 --}}
        @if(session()->get('admin_user_id') == 1)
        <div class="ibox-content m-b-sm border-bottom">
            <a href="{{ auto_url('system/role/index?all=1') }}" class="btn btn-w-m btn-info" >查看所有角色</a>
        </div>
        @endif
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
                                <th>标识</th>
                                <th data-toggle="true">名称</th>
                                <th>类别</th>
                                <th>类内分组</th>
                                <th data-hide="all">权限</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($list) == 0)
                                <tr><td colspan="100" style="text-align:center">暂无数据</td></tr>
                            @else
                                @foreach($list as $item)
                                    <tr>
                                        <td>{{ $item->alias }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{!! $presenter->formatType($item->type) !!}</td>
                                        <td>{{ $item->group }}</td>
                                        <td>
                                            @foreach($item->permission_grant as $permission)
                                                <span class="label label-primary">{{ $permission->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($item->users['pivot']['is_admin'])
                                                <a class="btn btn-xs btn-primary" href="{{ auto_url('system/role/add_page?role_id='.$item->role_id) }}">新建角色</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($item->users['pivot']['is_admin'])
                                        @foreach($item->children_role as $role)
                                            <tr style="color: #007FFF">
                                                <td>&emsp;&emsp;{{ $role->alias }}</td>
                                                <td>&emsp;&emsp;{{ $role->name }}</td>
                                                <td>{!! $presenter->formatType($role->type) !!}</td>
                                                <td>{{ $role->group }}</td>
                                                <td>
                                                    @foreach($role->permission_grant as $permission)
                                                        <span class="label label-primary">{{ $permission->name }}</span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <a class="btn btn-xs btn-warning" href="{{ auto_url('system/role/edit_page?role_id='.$role->role_id) }}"><i class="fa fa-pencil"></i></a>
                                                    <a class="btn btn-xs btn-danger" onclick="$.admin.tipBeforeAjax('{{ auto_url('system/role/delete?role_id='.$role->role_id) }}', '删除角色', '确认删除角色？该操作不可撤销', 'error')"><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
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