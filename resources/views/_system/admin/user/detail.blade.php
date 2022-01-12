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
                    <div class="ibox-content form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-2 col-md-2 col-lg-2 control-label">姓名</label>
                            <div class="col-sm-10 col-md-10 col-lg-10">
                                <div class="form-control" style="border: 0">{{ $adminUser->name }}</div>
                                <span class="help-block m-b-none"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 col-md-2 col-lg-2 control-label">手机</label>
                            <div class="col-sm-10 col-md-10 col-lg-10">
                                <div class="form-control" style="border: 0">{{ $adminUser->mobile }}</div>
                                <span class="help-block m-b-none"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 col-md-2 col-lg-2 control-label">邮箱</label>
                            <div class="col-sm-10 col-md-10 col-lg-10">
                                <div class="form-control" style="border: 0">{{ $adminUser->mail }}</div>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')

@stop