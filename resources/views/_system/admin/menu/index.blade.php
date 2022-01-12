@extends('layouts.app')

@section('title', '菜单')

@section('linksmiddle')
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/select2/select2.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/dualListbox/bootstrap-duallistbox.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/iconpicker/jquery.fonticonpicker.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/iconpicker/grey-theme/jquery.fonticonpicker.grey.min.css') !!}">
    <link rel="stylesheet" href="{!! auto_asset('css/plugins/fonts/font-awesome.min.css') !!}">
@stop

@section('content')
    {{-- 操作&数据 --}}
    <div class="wrapper wrapper-content animated fadeInRight ecommerce">
        <div class="row">
            <div class="col-md-5">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>菜单</h5>
                    </div>
                    <div class="ibox-content">

                        <div id="nestable-menu">
                            <button type="button" data-action="expand-all" class="btn btn-primary btn-sm">展开全部</button>
                            <button type="button" data-action="collapse-all" class="btn btn-primary btn-sm">折叠全部</button>
                            <button type="button" data-action="save-all" class="pull-right btn btn-warning btn-sm">保存</button>
                        </div>

                        <form action="{{ auto_url('system/menu/order') }}" method="post" style="display: none;"
                              id="menu-order-form" data-ajax-form="true">
                            <input type="hidden" name="order_json" />
                        </form>

                        {{-- tree --}}
                        <div class="dd" id="nestable2">
                            <ol class="dd-list">
                            @foreach($menus as $menu)
                                <li class="dd-item" data-id="{{ $menu['menu_id'] }}">
                                    <div class="dd-handle">
                                        <i class="{{ $menu['icon'] }}"></i>
                                        <strong>{{ $menu['title'] }}</strong>
                                        @if(!empty($menu['path']))
                                        <a href="{{ auto_url($menu['path']) }}" class="dd-nodrag">
                                            /{{ ltrim($menu['path'], '/') }}
                                        </a>
                                        @endif
                                        <span class="pull-right dd-nodrag">
                                            <a class="btn-warning btn btn-xs" onclick="getDetail('{{ auto_url('system/menu/detail?menu_id=' . $menu['menu_id']) }}')"><i class="fa fa-pencil"></i></a>
                                            <a class="btn-danger btn btn-xs"
                                               onclick="$.admin.tipBeforeAjax('{{ auto_url('system/menu/delete?menu_id=' . $menu['menu_id']) }}', '删除菜单', '确定删除该菜单？改选择无法撤销', 'error')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </span>
                                    </div>
                                    @if(!empty($menu['children']))
                                    <ol class="dd-list">
                                        @foreach($menu['children'] as $childMenu)
                                        <li class="dd-item" data-id="{{ $childMenu['menu_id'] }}">
                                            <div class="dd-handle">
                                                <i class="{{ $childMenu['icon'] }}"></i>
                                                <strong>{{ $childMenu['title'] }}</strong>
                                                @if(!empty($childMenu['path']))
                                                <a href="{{ auto_url($childMenu['path']) }}" class="dd-nodrag">
                                                    /{{ ltrim($childMenu['path'], '/') }}
                                                </a>
                                                @endif
                                                <span class="pull-right dd-nodrag">
                                                    <a class="btn-warning btn btn-xs" onclick="getDetail('{{ auto_url('system/menu/detail?menu_id=' . $childMenu['menu_id']) }}')"><i class="fa fa-pencil"></i></a>
                                                    <a class="btn-danger btn btn-xs"
                                                       onclick="$.admin.tipBeforeAjax( '{{ auto_url('system/menu/delete?menu_id=' . $childMenu['menu_id']) }}', '删除菜单', '确定删除该菜单？改选择无法撤销', 'error')">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </span>
                                            </div>
                                            @if(!empty($childMenu['children']))
                                            <ol class="dd-list">
                                            @foreach($childMenu['children'] as $grandChildMenu)
                                                <li class="dd-item" data-id="{{ $grandChildMenu['menu_id'] }}">
                                                    <div class="dd-handle">
                                                        <i class="{{ $grandChildMenu['icon'] }}"></i>
                                                        <strong>{{ $grandChildMenu['title'] }}</strong>
                                                        @if(!empty($grandChildMenu['path']))
                                                        <a href="{{ auto_url($grandChildMenu['path']) }}" class="dd-nodrag">
                                                            /{{ ltrim($grandChildMenu['path'], '/') }}
                                                        </a>
                                                        @endif
                                                        <span class="pull-right dd-nodrag">
                                                            <a class="btn-warning btn btn-xs" onclick="getDetail('{{ auto_url('system/menu/detail?menu_id=' . $grandChildMenu['menu_id']) }}')"><i class="fa fa-pencil"></i></a>
                                                            <a class="btn-danger btn btn-xs"
                                                               onclick="$.admin.tipBeforeAjax( '{{ auto_url('system/menu/delete?menu_id=' . $grandChildMenu['menu_id']) }}', '删除菜单', '确定删除该菜单？改选择无法撤销', 'error')">
                                                                <i class="fa fa-trash"></i>
                                                            </a>
                                                        </span>
                                                    </div>
                                                </li>
                                            @endforeach
                                            </ol>
                                        @endif
                                        </li>
                                        @endforeach
                                    </ol>
                                    @endif
                                </li>
                            @endforeach
                            </ol>
                        </div>
                        {{-- /tree --}}
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>新增 /编辑菜单</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ auto_url('system/menu/submit') }}" method="post" class="form-horizontal"
                              id="menu-edit-form" data-ajax-form="true">

                            {{-- 上级菜单 --}}
                            <div class="form-group">
                                <label class="col-sm-2 control-label">上级菜单</label>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <select class="form-control" id="form-parent-menu-id" name="parent_menu_id">
                                                <option value="0">
                                                    根目录
                                                </option>
                                                @foreach($menus as $menu)
                                                    <option value="{{ $menu['menu_id'] }}">
                                                        {{ $menu['title'] }}
                                                    </option>
                                                    @if(!empty($menu['children']))
                                                        @foreach($menu['children'] as $childrenMenu))
                                                            <option value="{{ $childrenMenu['menu_id'] }}">
                                                                &nbsp;&nbsp;&nbsp;&nbsp;{{ $childrenMenu['title'] }}
                                                            </option>
                                                            @if(!empty($childrenMenu['children']))
                                                                @foreach($childrenMenu['children'] as $grandChildMenu)
                                                                    <option value="{{ $grandChildMenu['menu_id'] }}">
                                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $grandChildMenu['title'] }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </select>
                                            <span class="help-block m-b-none">编辑模式下不可修改</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- /上级菜单 --}}

                            {{-- 标题 --}}
                            <div class="form-group">
                                <label class="col-sm-2 control-label">标题</label>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" id="form-title" name="title" required>
                                            <span class="help-block m-b-none"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- /标题 --}}

                            {{-- 图标 --}}
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="form-icon">图标</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input style="width: 180px" type="text" id="form-icon" name="icon" value="fa-bars" class="form-control icon" autocomplete="off" required />
                                    </div>
                                    <span class="help-block m-b-none"></span>
                                </div>
                            </div>
                            {{-- /图标 --}}

                            {{-- 路由 --}}
                            <div class="form-group">
                                <label class="col-sm-2 control-label">路由</label>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" id="form-path" name="path" autocomplete="off">
                                            <span class="help-block m-b-none"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- /路由 --}}

                            {{-- 提交按钮 --}}
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <input type="hidden" class="form-control" name="menu_id" id="form-menu-id" autocomplete="off">
                                    <a class="btn btn-default" onclick="resetTable()">重置</a>
                                    <button class="btn btn-primary" type="submit" data-id="menu-edit-form">提交</button>
                                </div>
                            </div>
                            {{-- /提交按钮 --}}

                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    {{-- /操作&数据 --}}
@stop

@section('scripts')
    {{-- Nestable List --}}
    <script src="{!! auto_asset('js/plugins/nestable/jquery.nestable.js') !!}"></script>
    {{-- Select2 --}}
    <script src="{!! auto_asset('js/plugins/select2/select2.full.min.js') !!}"></script>
    {{-- Dual Listbox --}}
    <script src="{!! auto_asset('js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') !!}"></script>
    {{-- Icon picker --}}
    <script src="{!! auto_asset('js/plugins/iconpicker/jquery.fonticonpicker.min.js') !!}"></script>
    <script src="{!! auto_asset('js/plugins/fonts/font-awesome.js') !!}"></script>

    <script>
        $(function () {
            $("#form-parent-menu-id").select2();

            $('#nestable2').nestable([]);

            $('#nestable-menu').on('click', function (e) {
                var target = $(e.target),
                    action = target.data('action');
                if (action === 'expand-all') {
                    $('.dd').nestable('expandAll');
                }
                if (action === 'collapse-all') {
                    $('.dd').nestable('collapseAll');
                }
                if (action === 'save-all') {
                    var serialize = $('#nestable2').nestable('serialize');
                    var order = JSON.stringify(serialize);
                    $('input[name="order_json"]').val(order);
                    $('#menu-order-form').submit();
                }
            });

            $('#form-icon').fontIconPicker({
                source: font_awesome
            });
        });

        var getDetail = function (url) {
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'json',
                error: function () {
                    $.admin.notify('数据获取失败', 'error');
                },
                success: function (json) {
                    $("#form-parent-menu-id").val(json.data.parent_menu_id);
                    $("#form-parent-menu-id").attr('disabled', true);
                    $("#form-title").val(json.data.title);
                    $("#form-icon").val(json.data.icon);
                    $(".selected-icon").html('<i class="' + json.data.icon + '"></i>');
                    $("#form-path").val(json.data.path);
                    $("#form-menu-id").val(json.data.menu_id);
                }
            });
        };

        var resetTable = function () {
            $("#form-parent-menu-id").val('0');
            $("#form-parent-menu-id").attr('disabled', false);
            $("#select2-form-parent-menu-id-container").html('根目录');
            $("#select2-form-parent-menu-id-container").attr('title', '根目录');
            $("#form-title").val('');
            $("#form-icon").val('');
            $(".selected-icon").html('<i class="fip-icon-block"></i>');
            $("#form-path").val('');
            $("#form-menu-id").val('');
        }
    </script>
@stop