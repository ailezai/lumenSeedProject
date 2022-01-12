@extends('layouts.app')

@section('title', '修改密码')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/iCheck/custom.css') !!}">
@stop

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    {{-- ibox-content --}}
                    <div class="ibox-content">
                        <form action="{{ auto_url('personal/password/submit') }}" method="post" class="form-horizontal"
                              id="page-edit-form" data-ajax-form="true">

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-password">当前密码</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="password" class="form-control" id="page-edit-form-password" name="old_password" autocomplete="off" required>
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

                            <div class="hr-line-dashed"></div>

                            <div class="form-group">
                                <div class="col-md-4 col-lg-4 col-md-offset-2 col-lg-offset-2">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
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

    <script>
        $(function () {
            $("#page-edit-form").validate({
                rules: {
                    password: {
                        required: true,
                        minlength: 6
                    }
                }
            });
        });
    </script>
@stop