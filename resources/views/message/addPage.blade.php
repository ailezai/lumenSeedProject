<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span aria-hidden="true">&times;</span>
        <span class="sr-only">Close</span>
    </button>
    <h4 class="modal-title">新增消息</h4>
</div>
<form action="{{ auto_url('message/miniapp/add_submit') }}" method="post" class="form-horizontal"
      id="modal-pjax-form" data-ajax-form="true">
    <div class="modal-body">
        {{-- 数据 --}}
        <div class="row">
            <div class="col-lg-12">

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-alias">标题</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-alias" name="title" autocomplete="off" required>
                        <span class="help-block m-b-none">新厂上架</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-alias">业务类型</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-alias" name="business_type" autocomplete="off" required>
                        <span class="help-block m-b-none">alipay_micro</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-alias">业务场景</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-alias" name="scence" autocomplete="off" required>
                        <span class="help-block m-b-none">ali_micro_zhaogongzuo_remind_msg</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-alias">keyword1</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-alias" name="keyword1" autocomplete="off" required>
                        <span class="help-block m-b-none">新厂上架-圆通速递</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-alias">keyword2</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-alias" name="keyword2" autocomplete="off">
                        <span class="help-block m-b-none">临时工；18-45岁；15元/小时；提供住宿和工作餐；</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-alias">keyword3</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-alias" name="keyword3" autocomplete="off">
                        <span class="help-block m-b-none"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-alias">keyword4</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-alias" name="keyword4" autocomplete="off">
                        <span class="help-block m-b-none"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-alias">链接</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-alias" name="url" autocomplete="off">
                        <span class="help-block m-b-none">pages/detail/detail?factoryId%3D629</span>
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