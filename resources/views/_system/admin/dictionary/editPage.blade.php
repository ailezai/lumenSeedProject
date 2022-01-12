<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span aria-hidden="true">&times;</span>
        <span class="sr-only">Close</span>
    </button>
    <h4 class="modal-title">编辑字典</h4>
</div>
<form action="{{ auto_url('system/dictionary/edit_submit') }}" method="post" class="form-horizontal"
      id="modal-pjax-form" data-ajax-form="true">
    <div class="modal-body">
        {{-- 数据 --}}
        <div class="row">
            <div class="col-lg-12">

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-name">名称</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-name" name="name" value="{{ $dictionary->name }}" readonly>
                        <span class="help-block m-b-none">请使用英文大写字母表示</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-desc">描述</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <input type="text" class="form-control" id="pjax-form-desc" name="desc" value="{{ $dictionary->desc }}" required>
                        <span class="help-block m-b-none"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-desc">字典</label>
                    <div class="col-sm-10 col-md-10 col-lg-10">
                        <button type="button" class="btn btn-primary" onclick="DictInfo.addItem()">添加</button>
                        <table class="footable table table-stripped toggle-arrow-tiny">
                            <thead>
                            <tr>
                                <th>键</th>
                                <th>值</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody id="admin-dictionary">
                            @php($count = 0)
                            @foreach(json_decode($dictionary->dictionary, true) as $key => $value)
                                @php($count++)
                                <tr name="dictItem" id="dictItem{{ $count }}">
                                    <td><input class="form-control" type="text" name="key[]" value="{{ $key }}" /></td>
                                    <td><input class="form-control" type="text" name="value[]" value="{{ $value }}" /></td>
                                    <td>
                                        <button class="btn-danger btn btn-xs" onclick="DictInfo.deleteItem(event)"><i class="fa fa-times"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <input type="hidden" id="itemSize" value={{ $count }} />
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="hidden" name="id" value="{{ $dictionary->id }}">
        <button type="button" class="btn btn-white" data-dismiss="modal">返回</button>
        <button type="submit" class="btn btn-primary" data-id="modal-pjax-form">提交</button>
    </div>
</form>

<script type="text/template" id="itemTemplate">
    <tr name="dictItem" id="dictItem">
        <td><input class="form-control" type="text" name="key[]" /></td>
        <td><input class="form-control" type="text" name="value[]" /></td>
        <td>
            <button class="btn-danger btn btn-xs" onclick="DictInfo.deleteItem(event)"><i class="fa fa-times"></i></button>
        </td>
    </tr>
</script>

<script>
    /**
     * 初始化字典详情对话框
     */
    var DictInfo = {
        count: $("#itemSize").val(),
        dictName: '',     // 字典的名称
        mutiString: '',   // 拼接字符串内容(拼接字典条目)
        itemTemplate: $("#itemTemplate").html()
    };

    /**
     * item获取新的id
     */
    DictInfo.newId = function () {
        if(this.count == undefined){
            this.count = 0;
        }
        this.count = parseInt(this.count) + 1;
        return "dictItem" + this.count;
    };

    /**
     * 添加条目
     */
    DictInfo.addItem = function () {
        $("#admin-dictionary").append(this.itemTemplate);
        $("#dictItem").attr("id", this.newId());
    };

    /**
     * 删除item
     */
    DictInfo.deleteItem = function (event) {
        event = event ? event : window.event;
        var object = event.srcElement ? event.srcElement : event.target;
        var obj = $(object);
        obj = obj.is('button') ? obj : obj.parent();
        obj.parent().parent().remove();
    };
</script>