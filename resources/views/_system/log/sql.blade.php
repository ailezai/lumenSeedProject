@extends('layouts.app')

@section('title', 'SQL日志')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
@stop

@inject('presenter', 'System\Presenters\Log\LogSqlPresenter')
@section('content')
    {{-- 操作&搜索&数据 --}}
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">

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
                        <form action="{{ auto_url($request->path()) }}" id="list-search-form" method="get">
                            <div class="row">
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-trace-id">Trace-id</label>
                                        <input class="form-control" id="search-trace-id" name="trace_id" value="{{ $request->input('trace_id', '') }}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group" id="data_expire_time">
                                        <label class="font-bold">SQL耗时</label>
                                        <div class="input-group">
                                            <input type="text" class="input-sm form-control" name="time1" value="{{ $request->input('time1', '') }}" autocomplete="off" />
                                            <span class="input-group-addon">到</span>
                                            <input type="text" class="input-sm form-control" name="time2" value="{{ $request->input('time2', '') }}" autocomplete="off" />
                                        </div>
                                    </div>
                                </div>

                                {{-- 搜索&清空 --}}
                                <div class="col-md-3 col-lg-3 view-page-search-form pull-right">
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
                                <th data-toggle="true" style="width: 6%">ID</th>
                                <th data-hide="all">Trace-id</th>
                                <th>SQL语句</th>
                                <th data-hide="all">绑定数据</th>
                                <th>耗时</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($list) == 0)
                                <tr><td colspan="100" style="text-align:center">暂无数据</td></tr>
                            @else
                                @foreach($list as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->trace_id }}</td>
                                        <td>{{ $item->query }}</td>
                                        <td>
                                            <pre>{{ json_encode(json_decode($item->bindings), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </td>
                                        <td>{!! $presenter->formatTime($item->time) !!}</td>
                                    </tr>
                                @endforeach
                            @endif
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
    {{-- 数据 --}}
@stop

@section('scripts')
    {{-- Jquery Validate --}}
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.min.js') !!}"></script>
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.zh-CN.min.js') !!}"></script>
    {{-- FooTable --}}
    <script src="{!! auto_asset('js/plugins/footable/footable.all.min.js') !!}"></script>
    <script>
        $(function () {
            $("#list-search-form").validate({
                rules: {
                    time1: {
                        number: true
                    },
                    time2: {
                        number: true
                    }
                }
            });

            // footable配置
            $('.footable').footable({
                "paginate": false,
                "sort": false
            });
        });
    </script>
@stop