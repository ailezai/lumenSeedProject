<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span aria-hidden="true">&times;</span>
        <span class="sr-only">Close</span>
    </button>
    <h4 class="modal-title">重置密码</h4>
</div>
<form action="{{ auto_url('system/user/reset') }}" method="post" class="form-horizontal"
      id="modal-pjax-form" data-ajax-form="true">
    <div class="modal-body">
        {{-- 数据 --}}
        <div class="row">
            <div class="col-lg-12">

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

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="hidden" name="admin_user_id" value="{{ $adminUserId }}">
        <button type="button" class="btn btn-white" data-dismiss="modal">返回</button>
        <button type="submit" class="btn btn-primary" data-id="modal-pjax-form">提交</button>
    </div>
</form>

{{-- Select2 --}}
<link rel="stylesheet" href="{!! auto_asset('css/plugins/select2/select2.min.css') !!}">
<script src="{!! auto_asset('js/plugins/select2/select2.full.min.js') !!}"></script>
<script>
    $("#pjax-form-method").select2();
</script>