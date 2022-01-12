@extends('layouts.app')

@section('title', '代码生成')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! asset('css/plugins/select2/select2.min.css') !!}">
    <link rel="stylesheet" href="{!! asset('css/plugins/jquery-pretty-radio-checkbox/jquery-labelauty.css') !!}">
    <link rel="stylesheet" href="{!! asset('css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') !!}">
@stop

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        <form action="{{ url('system/setting/code/generate') }}" method="post" class="form-horizontal"
              id="company-edit-form" data-ajax-form="true">
            <div class="ibox">
                {{-- ibox-content --}}
                <div class="ibox-content form-horizontal">

                    <div class="form-group">
                        <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-db">数据库</label>
                        <div class="col-sm-10 col-md-10 col-lg-10">
                            <select class="form-control" id="page-edit-form-db" name="db" required>
                                @foreach($db as $item)
                                    <option value="{{ $item }}" @if('mysql' == $item) selected @endif>{{ $item }}</option>
                                @endforeach
                            </select>
                            <span class="help-block m-b-none"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-model_path">Model路径</label>
                        <div class="col-sm-10 col-md-10 col-lg-10">
                            <input type="text" class="form-control" id="page-edit-form-model_path" name="model_path" value="{{ $modelPath ?? '' }}">
                            <span class="help-block m-b-none">置空则不生成</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-repository_path">Repository路径</label>
                        <div class="col-sm-10 col-md-10 col-lg-10">
                            <input type="text" class="form-control" id="page-edit-form-repository_path" name="repository_path" value="{{ $repositoryPath ?? '' }}">
                            <span class="help-block m-b-none">置空则不生成</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-service_path">Service路径</label>
                        <div class="col-sm-10 col-md-10 col-lg-10">
                            <input type="text" class="form-control" id="page-edit-form-service_path" name="service_path" value="{{ $servicePath ?? '' }}">
                            <span class="help-block m-b-none">置空则不生成</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-controller_path">Controller路径</label>
                        <div class="col-sm-10 col-md-10 col-lg-10">
                            <input type="text" class="form-control" id="page-edit-form-controller_path" name="controller_path" value="{{ $controllerPath ?? '' }}">
                            <span class="help-block m-b-none">置空则不生成</span>
                        </div>
                    </div>

                    <div class="form-group" id="view_type">
                        <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-view_type">视图生成方式</label>
                        <div class="col-sm-10 col-md-10 col-lg-10">
                            <div class="radio radio-primary">
                                <input type="radio" name="view_type" value="NONE" checked required><label>不生成</label><br />
                                <input type="radio" name="view_type" value="PAGE" required><label>生成新页面</label><br />
                                <input type="radio" name="view_type" value="PJAX" required><label>生成pjax页面</label><br />
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="resource_path" style="display: none">
                        <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-resource_path">view页面文件位置</label>
                        <div class="col-sm-10 col-md-10 col-lg-10">
                            <input type="text" class="form-control" id="page-edit-form-resource_path" name="resource_path" value="{{ $resourcePath ?? '' }}">
                            <span class="help-block m-b-none"></span>
                        </div>
                    </div>

                    <div class="form-group" id="root_route" style="display: none">
                        <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-root_route">view页面根路由请求</label>
                        <div class="col-sm-10 col-md-10 col-lg-10">
                            <input type="text" class="form-control" id="page-edit-form-root_route" name="root_route" value="{{ $rootRoute ?? '' }}">
                            <span class="help-block m-b-none"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-table">表</label>
                        <div class="col-sm-10 col-md-10 col-lg-10">
                            <input type="text" class="form-control" id="page-edit-form-table" name="table" required>
                            <span class="help-block m-b-none"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-results">结果</label>
                        <div class="col-sm-10 col-md-10 col-lg-10">
                            <textarea class="form-control" id="page-edit-form-results" rows="5" disabled></textarea>
                        </div>
                    </div>


                    <div class="form-group">
                        <div class="col-md-4 col-lg-4 col-md-offset-2 col-lg-offset-2">
                            <a href="javascript:history.back(-1);" class="btn btn-default">取消</a>
                            <button class="btn btn-primary" type="submit" data-id="page-edit-form">提交</button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
@stop

@section('scripts')
    <!-- Select2 -->
    <script src="{!! asset('js/plugins/select2/select2.full.min.js') !!}"></script>

    <script src="{!! asset('js/plugins/jquery-pretty-radio-checkbox/jquery-labelauty.js') !!}"></script>

    <script>
        $(function () {

            $("#page-edit-form-db").select2();
            $("#page-edit-form-view_type").select2();

            // ichecks配置
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green'
            });
        });

        var ajaxSuccessCallback = function (json) {
            var info = '';
            json.data.forEach(function (value) {
                info += value + "\n";
            });
            $("#page-edit-form-results").val(info);
        };

        $('input[name="view_type"]').bind("change", function () {
            var type = $('input[name="view_type"]:checked').val();
            if (type === "PAGE" || type === "PJAX") {
                $("#resource_path").css("display", "block");
                $("#root_route").css("display", "block");
            } else {
                $("#resource_path").css("display", "none");
                $("#root_route").css("display", "none");
            }
        });
    </script>
@stop