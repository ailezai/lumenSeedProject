@extends('layouts.app')

@section('title', '添加管理员')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/select2/select2.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/dualListbox/bootstrap-duallistbox.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/iCheck/custom.css') !!}">
@stop

@inject('presenter', 'System\Presenters\Admin\AdminUserPresenter')
@section('content')
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    {{-- ibox-content --}}
                    <div class="ibox-content">
                        <form action="{{ auto_url('system/user/add_submit') }}" method="post" class="form-horizontal"
                              id="page-edit-form" data-ajax-form="true">

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-username">登录名</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="text" class="form-control" id="page-edit-form-username" name="username" autocomplete="off" required>
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-password">密码</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="password" class="form-control" id="page-edit-form-password" name="password" autocomplete="off" required>
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-confirm-password">重复密码</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="password" class="form-control" id="page-edit-form-confirm-password" name="password_confirmation" autocomplete="off" required>
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-name">姓名</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="text" class="form-control" id="page-edit-form-name" name="name" autocomplete="off" required>
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-mobile">手机</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="text" class="form-control" id="page-edit-form-mobile" name="mobile" autocomplete="off">
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-mail">邮箱</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="email" class="form-control" id="page-edit-form-mail" name="mail" autocomplete="off">
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-role">角色</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <table class="footable table table-stripped toggle-arrow-tiny">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>加入角色</th>
                                            <th>设为管理员</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($roles as $item)
                                            <tr>
                                                <td>{{ $item->name }}</td>
                                                <td><input type="checkbox" class="i-checks" name="role[]" value="{{ $item->role_id }}"></td>
                                                <td></td>
                                            </tr>
                                            @if($item->users['pivot']['is_admin'])
                                                @foreach($item->children_role as $role)
                                                    <tr>
                                                        <td>{{ $role->name }}</td>
                                                        <td><input type="checkbox" class="i-checks" name="role[]" value="{{ $role->role_id }}"></td>
                                                        <td><input type="checkbox" class="i-checks" name="role_admin[]" value="{{ $role->role_id }}"></td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>

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
    {{-- iCheck --}}
    <script src="{!! auto_asset('js/plugins/iCheck/icheck.min.js') !!}"></script>

    <script>
        $(function () {
            $("#page-edit-form").validate({
                rules: {
                    username: {
                        required: true
                    },
                    password: {
                        required: true,
                        minlength: 6
                    },
                    name: {
                        required: true
                    }
                }
            });

            $("#page-edit-form-type").select2();

            // ichecks配置
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green'
            });

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