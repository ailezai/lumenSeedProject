@extends('layouts.app')

@section('title', '支付宝小程序发送管理')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
@stop

@section('content')
    {{-- 操作&搜索&数据 --}}
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        <div class="ibox-content m-b-sm border-bottom">
            <a data-href="{{ auto_url('message/miniapp/add_page') }}" class="btn btn-w-m btn-info" data-pjax-request="true">新增</a>
        </div>

        {{-- 数据 --}}
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        {{-- table --}}
                        <table class="footable table table-stripped toggle-arrow-tiny">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>创建日期</th>
                                <th>模板活动标题</th>
                                <th>模板内容</th>
                                <th>发送用户范围</th>
                                <th>发送用户人数</th>
                                <th>发送状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($list) == 0)
                                <tr><td colspan="100" style="text-align:center">暂无数据</td></tr>
                            @else
                                @foreach($list as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->create_time }}</td>
                                        <td>{{ $item->title }}</td>
                                        <td>{!! $item->desc_content !!}</td>
                                        <td>{{ $item->send_time }}</td>
                                        <td>{{ $item->send_number }}</td>
                                        <td>
                                            @if($item->status == 'APPLY')
                                                未发送
                                            @elseif($item->status == 'SUCCESS')
                                                已发送
                                            @elseif($item->status == 'FAIL')
                                                发送失败
                                            @endif
                                        <td>
                                            @if($item->status == 'APPLY')
                                                <a class="btn btn-xs btn-primary" onclick="$.admin.tipBeforeAjax('{{ auto_url('message/miniapp/send_msg?id='.$item->id) }}', '发送消息', '确认发送消息？该操作不可撤销', 'success')">
                                                    <i class="fa fa-check"></i>发送</a>

                                                <a class="btn btn-xs btn-danger" onclick="$.admin.tipBeforeAjax('{{ auto_url('message/miniapp/del?id='.$item->id) }}', '删除消息', '确认删除消息？该操作不可撤销', 'error')">
                                                    <i class="fa fa-trash"></i>删除</a>

                                            @endif
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
    </div>
    {{-- 数据 --}}
@stop

@section('scripts')
    {{-- Jquery Validate --}}
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.min.js') !!}"></script>
    <script src="{!! auto_asset('js/plugins/validate/jquery.validate.zh-CN.min.js') !!}"></script>
    {{-- FooTable --}}
    <script src="{!! auto_asset('js/plugins/footable/footable.all.min.js') !!}"></script>
    {{-- Select2 --}}
    <script src="{!! auto_asset('js/plugins/select2/select2.full.min.js') !!}"></script>

    <script>
        $(function () {

            // footable配置
            $('.footable').footable({
                "paginate": false,
                "sort": false
            });
        });
    </script>
@stop