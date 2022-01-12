@extends('layouts.app')

@section('title', '个人信息')

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
                        <form action="{{ auto_url('personal/info/submit') }}" method="post" class="form-horizontal"
                              id="page-edit-form" data-ajax-form="true">

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-name">姓名</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="text" class="form-control" id="page-edit-form-name" name="name" value="{{ $adminUser->name }}" autocomplete="off" required>
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-mobile">手机</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="text" class="form-control" id="page-edit-form-mobile" name="mobile" value="{{ $adminUser->mobile }}" autocomplete="off">
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-mail">邮箱</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    <input type="email" class="form-control" id="page-edit-form-mail" name="mail" value="{{ $adminUser->mail }}" autocomplete="off">
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label">角色</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    @php($roles = session()->get('admin_user_role_ids'))
                                    @foreach($adminUser->role_grant as $item)
                                        @if(in_array($item->role_id, $roles))
                                            <p>
                                                <button type="button" class="btn btn-sm btn-primary">{{ $item->name }}</button>
                                                @if ($item->pivot->is_admin == 1)
                                                    <button type="button" class="btn btn-sm btn-success">管理员</button>
                                                @endif
                                            </p>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-md-2 col-lg-2 control-label">其他权限</label>
                                <div class="col-sm-10 col-md-10 col-lg-10">
                                    @foreach($adminUser->permission_grant as $item)
                                        <button type="button" class="btn btn-sm btn-primary">{{ $item->name }}</button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>

                            <div class="form-group">
                                <div class="col-md-4 col-lg-4 col-md-offset-2 col-lg-offset-2">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <a href="javascript:history.back(-1);" class="btn btn-default">取消</a>
                                    <button class="btn btn-primary" type="submit" data-id="page-edit-form">保存</button>
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
                    name: {
                        required: true
                    }
                }
            });
        });
    </script>

@stop