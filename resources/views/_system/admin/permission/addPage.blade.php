<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span aria-hidden="true">&times;</span>
        <span class="sr-only">Close</span>
    </button>
    <h4 class="modal-title">新增权限</h4>
</div>
<form action="{{ auto_url('system/permission/add_submit') }}" method="post" class="form-horizontal"
      id="modal-pjax-form" data-ajax-form="true">
    <div class="modal-body">
        {{-- 数据 --}}
        <div class="row">
            <div class="col-lg-12">

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-alias">标识</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-alias" name="alias" autocomplete="off" required>
                        <span class="help-block m-b-none">请用英文标识，'.'分割层级，'*'通配</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-name">名称</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-name" name="name" autocomplete="off" required>
                        <span class="help-block m-b-none"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-method">请求方法</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <select class="form-control" id="pjax-form-method" name="methods" autocomplete="off" required>
                            @foreach($requestMethod as $item)
                                <option value="{{ $item[0] }}">{{ $item[1] }}</option>
                            @endforeach
                        </select>
                        <span class="help-block m-b-none"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-path">路由</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <textarea id="pjax-form-path"  name="path" class="form-control" rows="5" style="resize: none;"></textarea>
                        <span class="help-block m-b-none"></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="modal-footer">
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