@extends('layouts.app')

@section('title', '字典表')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
@stop

@section('content')
    {{-- 操作&搜索&数据 --}}
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        {{-- 操作 --}}
        <div class="ibox-content m-b-sm border-bottom">
            <a data-href="{{ auto_url('system/dictionary/add_page') }}" class="btn btn-w-m btn-info" data-pjax-request="true">新增字典</a>
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
                        <form action="{{ auto_url($request->path()) }}" id="list-search-form" method="get">
                            <div class="row">
                                <div class="col-md-3 col-lg-3 view-page-search-form">
                                    <div class="form-group">
                                        <label class="font-bold" for="search-name">名称</label>
                                        <input class="form-control" id="search-name" name="name" value="{{ $request->input('name', '') }}" autocomplete="off">
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
                                <th>名称</th>
                                <th>描述</th>
                                <th>字典</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($list) == 0)
                                <tr><td colspan="100" style="text-align:center">暂无数据</td></tr>
                            @else
                                @foreach($list as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->desc }}</td>
                                        <td>
                                            <pre>{{ json_encode(json_decode($item->dictionary), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-warning" data-href="{{ auto_url('system/dictionary/edit_page?id='.$item->id) }}" data-pjax-request="true"><i class="fa fa-pencil"></i></a>
                                        </td>
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
        {{-- 数据 --}}
    </div>
    {{-- 操作&搜索&数据 --}}
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