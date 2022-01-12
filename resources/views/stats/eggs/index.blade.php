@extends('layouts.app')

@section('title', '砸金蛋报表')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/select2/select2.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/footable/footable.core.css') !!}">
@stop

@section('content')
    {{-- 操作&搜索&数据 --}}
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">

        {{-- 数据 --}}
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        {{-- table --}}
                        <table class="footable table table-stripped toggle-arrow-tiny">
                            <thead>
                            <tr>
                                <th>日期</th>
                                <th>砸中钱的人数</th>
                                <th>砸中钱的次数</th>
                                <th>奖池总消耗</th>
                                <th>首次奖池消耗</th>
                                <th>正常奖池消耗</th>
                                <th>连续奖池消耗</th>
                                <th>订阅生活号模版人数</th>
                                <th>领蛋人数</th>
                                <th>砸蛋次数</th>
                                <th>砸中蛋总数</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($list) == 0)
                                <tr><td colspan="100" style="text-align:center">暂无数据</td></tr>
                            @else
                                @foreach($list as $item)
                                    <tr>
                                        <td>@if(array_key_exists("d",$item)){{ $item['d'] }}@endif</td>
                                        <td>@if(array_key_exists("smash_amount_count",$item)){{ $item['smash_amount_count'] }}@endif</td>
                                        <td>@if(array_key_exists("smash_eggs_num",$item)){{ $item['smash_eggs_num'] }}@endif</td>
                                        <td>@if(array_key_exists("first_amount",$item)){{ ($item['first_amount']+(array_key_exists("normal_amount",$item)?$item['normal_amount']:0))/100 }}@endif</td>
                                        <td>@if(array_key_exists("first_amount",$item)){{ $item['first_amount']/100 }}@endif</td>
                                        <td>@if(array_key_exists("normal_amount",$item)){{ $item['normal_amount']/100 }}@endif</td>
                                        <td>@if(array_key_exists("seven_amount",$item)){{ $item['seven_amount']/100 }}@endif</td>
                                        <td>@if(array_key_exists("subscription_num",$item)){{ $item['subscription_num'] }}@endif</td>
                                        <td>@if(array_key_exists("get_eggs_people_num",$item)){{ $item['get_eggs_people_num'] }}@endif</td>
                                        <td>@if(array_key_exists("smash_amount_num",$item)){{ $item['smash_amount_num'] }}@endif</td>
                                        <td>@if(array_key_exists("smash_eggs_num",$item)){{ $item['smash_eggs_num'] }}@endif</td>
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
            $("#list-search-form").validate({
                rules: {

                }
            });

            // select2
            $("#search-status").select2();

            // footable配置
            $('.footable').footable({
                "paginate": false,
                "sort": false
            });
        });
    </script>
@stop