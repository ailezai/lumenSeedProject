@extends('layouts.app')

@section('title', '样例')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/select2/select2.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/iCheck/custom.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
@stop

@section('linksbottom')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/datapicker/datepicker3.css') !!}">
@stop

{{--@inject('presenter', 'App\Presenters\Presenter')--}}
@section('content')
    {{-- 操作&搜索&数据 --}}
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        {{-- 操作 --}}
        <div class="ibox-content m-b-sm border-bottom">
            <a href="#" class="btn btn-w-m btn-info">按钮</a>
        </div>
        {{-- 操作 --}}

        {{-- 搜索 --}}
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="ibox">
                    <div class="ibox-title collapse-link">
                        <h5>搜索</h5>
                        <div class="ibox-tools">
                            <a><i class="fa fa-chevron-up"></i></a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <form action="" method="get">
                            <div class="row">
                                {{-- 文本搜索 --}}
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-name">文本搜索</label>
                                        <input class="form-control" id="search-name" name="name" autocomplete="off">
                                    </div>
                                </div>
                                {{-- /文本搜索 --}}

                                {{-- 时间范围搜索 --}}
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-range-time">时间范围搜索</label>
                                        <div class="form-group" id="search-range-time">
                                            <div class="input-daterange input-group">
                                                <input type="text" class="input-sm form-control"
                                                       name="range_time_min"
                                                       autocomplete="off"/>
                                                <span class="input-group-addon">到</span>
                                                <input type="text" class="input-sm form-control"
                                                       name="range_time_max"
                                                       autocomplete="off"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- /时间范围搜索 --}}

                                {{-- 单选 --}}
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-select-one">单选</label>
                                        <select class="form-control" id="search-select-one" name="select-one">
                                            <option value="1">单选1</option>
                                            <option value="2">单选2</option>
                                            <option value="3">单选3</option>
                                            <option value="4">单选4</option>
                                            <option value="5">单选5</option>
                                        </select>
                                    </div>
                                </div>
                                {{-- /单选 --}}

                                {{-- 搜索&清空 --}}
                                <div class="col-md-3 pull-right">
                                    <div class="form-group pull-right">
                                        <label class="font-bold"></label>
                                        <div>
                                            <button type="submit" class="btn btn-w-m btn-primary">搜索</button>&nbsp;
                                            <button type="reset" class="btn btn-w-m btn-default">清空</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- /搜索&清空 --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- 搜索 --}}

        {{-- 数据 --}}
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        {{-- table --}}
                        <table class="footable table table-stripped toggle-arrow-tiny">
                            <thead>
                            <tr>
                                <th>数据标题1</th>
                                <th>数据标题2</th>
                                <th>数据标题3</th>
                                <th>数据标题4</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>数据正文1</td>
                                    <td>数据正文2</td>
                                    <td>数据正文3</td>
                                    <td>数据正文4</td>
                                </tr>
                            </tbody>
                            <tfoot>
                            @if(is_object($list)
                                && ($list instanceof \Illuminate\Pagination\Paginator
                                    || $list instanceof \Illuminate\Pagination\LengthAwarePaginator))
                                <tr>
                                    <td colspan="20">
                                        <ul class="pull-right">
                                            每页{{ $list->perPage() }}条，当前页{{ $list->count() }}条，共{{ $list->total() }}条
                                            {{ $list }}
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- 操作&搜索&数据 --}}
@stop

@section('scripts')
    <style>
        .input-sm {
            height: 28px;
        }
        .input-daterange .input-group-addon {
            padding: 3px 5px;
        }

        .col-lg-12 .ibox.border-bottom {
            margin-bottom: 15px;
        }
    </style>

    {{-- Jquery Validate --}}
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.min.js') !!}"></script>
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.zh-CN.min.js') !!}"></script>
    {{-- FooTable --}}
    <script src="{!! auto_asset('js/plugins/footable/footable.all.min.js') !!}"></script>
    <!-- Select2 -->
    <script src="{!! auto_asset('js/plugins/select2/select2.full.min.js') !!}"></script>
    <!-- Data picker -->
    <script src="{!! auto_asset('js/plugins/datapicker/bootstrap-datepicker.js') !!}"></script>
    <script src="{!! auto_asset('js/plugins/datapicker/bootstrap-datepicker.zh-CN.min.js') !!}"></script>
    <!-- Date range picker -->
    <script src="{!! auto_asset('js/plugins/daterangepicker/daterangepicker.js') !!}"></script>

    <script>
        $(document).ready(function() {

            // 搜索隐藏
            $('.collapse-link').click();

            // footable配置
            $('.footable').footable({
                "paginate": false,
                "sort": false
            });

            // 单/多选框
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green'
            });

            $('#search-search-range-time').find('.input-daterange').datepicker({
                language: 'zh-CN',
                format: 'yyyy-mm-dd',
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true
            });

            $("#search-select-one").select2();
        });
    </script>
@stop