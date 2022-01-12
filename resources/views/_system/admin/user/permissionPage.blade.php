@extends('layouts.app')

@section('title', '独立授权')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/dualListbox/bootstrap-duallistbox.min.css') !!}">
@stop

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    {{-- ibox-content --}}
                    <div class="ibox-content">
                        <form action="{{ auto_url('system/user/permission_submit') }}" method="post" class="form-horizontal"
                              id="page-edit-form" data-ajax-form="true">

                            <div class="form-group">
                                <label class="col-sm-2 control-label">授予权限</label>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-9">
                                            <select class="form-control dual_select" id="page-edit-form-permission-grant" multiple name="grant[]">
                                                @php($grant = array_column($adminUser->permission_grant->toArray(), 'permission_id'))
                                                @foreach($permission as $item)
                                                    <option value="{{ $item->permission_id }}" @if(in_array($item->permission_id, $grant)) selected @endif>{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">禁用权限</label>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-9">
                                            <select class="form-control dual_select" id="page-edit-form-permission-forbid" multiple name="forbid[]">
                                                @php($forbid = array_column($adminUser->permission_forbid->toArray(), 'permission_id'))
                                                @foreach($permission as $item)
                                                    <option value="{{ $item->permission_id }}" @if(in_array($item->permission_id, $forbid)) selected @endif>{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>

                            <input type="hidden" name="admin_user_id" value="{{ $adminUser->admin_user_id }}">

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
    {{-- Dual Listbox --}}
    <script src="{!! auto_asset('js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') !!}"></script>

    <script>
        $(function () {
            $("#page-edit-form").validate({
                rules: {
                    name: {
                        required: true
                    }
                }
            });

            $('#page-edit-form-permission-grant').bootstrapDualListbox({
                nonSelectedListLabel: '未添加授予权限',
                selectedListLabel: '已添加授予权限',
                selectorMinimalHeight: 150
            });

            $('#page-edit-form-permission-forbid').bootstrapDualListbox({
                nonSelectedListLabel: '未添加禁止权限',
                selectedListLabel: '已添加禁止权限',
                selectorMinimalHeight: 150
            });
        });
    </script>
@stop