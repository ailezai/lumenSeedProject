@extends('layouts.app')

@section('title', '编辑角色')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/select2/select2.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/dualListbox/bootstrap-duallistbox.min.css') !!}">
@stop

@inject('presenter', 'System\Presenters\Admin\AdminRolePresenter')
@section('content')
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    {{-- ibox-content --}}
                    <div class="ibox-content">
                        <form action="{{ auto_url('system/role/edit_submit') }}" method="post" class="form-horizontal"
                              id="page-edit-form" data-ajax-form="true">

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-alias">标识</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="text" class="form-control" id="page-edit-form-alias" name="alias" value="{{ $role->alias }}" autocomplete="off" required>
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-name">名称</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="text" class="form-control" id="page-edit-form-name" name="name" value="{{ $role->name }}" autocomplete="off" required>
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-type">类别</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <select class="form-control" id="page-edit-form-type" name="type" required>
                                        @foreach($type as $item)
                                            <option value="{{ $item[0] }}" @if($role->type == $item[0]) selected @endif>{{ $item[1] }}</option>
                                        @endforeach
                                    </select>
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-group">类内分组</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="text" class="form-control" id="page-edit-form-group" name="group" value="{{ $role->group }}" autocomplete="off" required>
                                    <span class="help-block m-b-none">请使用英文大写和'_'填写</span>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">权限</label>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-9">
                                            <select class="form-control dual_select" id="page-edit-form-permission" multiple name="permission[]">
                                                @php($rolePermissionIds = array_column($role->permission_grant->toArray(), 'permission_id'))
                                                @foreach($parentRole->permission_grant as $item)
                                                    <option value="{{ $item->permission_id }}" @if(in_array($item->permission_id, $rolePermissionIds)) selected @endif>{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>

                            <input type="hidden" name="role_id" value="{{ $role->role_id }}" readonly>
                            <input type="hidden" name="parent_role_id" value="{{ $role->parent_role_id }}" readonly>

                            <div class="form-group">
                                <div class="col-md-4 col-lg-4 col-md-offset-2 col-lg-offset-2">
                                    <a href="javascript:history.back(-1);" class="btn btn-default">取消</a>
                                    <button class="btn btn-primary" type="submit" data-id="page-edit-form">提交</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    {{-- Jquery Validate --}}
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.min.js') !!}"></script>
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.zh-CN.min.js') !!}"></script>
    {{-- FooTable --}}
    <script src="{!! auto_asset('js/plugins/footable/footable.all.min.js') !!}"></script>
    {{-- Select2 --}}
    <script src="{!! auto_asset('js/plugins/select2/select2.full.min.js') !!}"></script>
    {{-- Dual Listbox --}}
    <script src="{!! auto_asset('js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') !!}"></script>

    <script>
        $(function () {
            $("#page-edit-form").validate({
                rules: {
                    alias: {
                        required: true
                    },
                    name: {
                        required: true
                    },
                    type: {
                        required: true
                    }
                }
            });

            // select2
            $("#page-edit-form-type").select2();

            $('#page-edit-form-permission').bootstrapDualListbox({
                nonSelectedListLabel: '未添加权限',
                selectedListLabel: '已添加权限',
                selectorMinimalHeight: 150
            });

            // footable配置
            $('.footable').footable({
                "paginate": false,
                "sort": false
            });
        });
    </script>
@stop