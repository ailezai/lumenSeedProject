<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            {{-- 账号信息 --}}
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear">
                            <span class="block m-t-xs">
                                <strong class="font-bold">
                                    {{ session()->get('admin_user_name') }}
                                </strong>
                            </span>
                            <span class="text-muted text-xs block">
                                {{ session()->get('admin_user_role') }}
                                <b class="caret"></b>
                            </span>
                        </span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="{{ auto_url('personal/info/index') }}">个人信息</a></li>
                        <li><a href="{{ auto_url('personal/password/index') }}">修改密码</a></li>
                        {{--<li><a href="{{ auto_url('jwt_redirect?audience=DEMO') }}" target="_blank">DEMO</a></li>--}}
                    </ul>
                </div>
                <div class="logo-element">
                    {{ config('webConfig.view.mini_logo') }}
                </div>
            </li>
            {{-- /账号信息 --}}
            {{-- 菜单 --}}
            <li>
                <form>
                    <input type="text" id="left-navigation-search" placeholder="搜索菜单..." style="background: none">
                </form>
            </li>
            @foreach(session()->get('menu', []) as $menu)
            <li data-type="left-navigation" @if(get_active_menus($menu['active'])) class="active" @endif>
                <a href="@if(empty($menu['children']) && !empty($menu['url'])) {{ auto_url($menu['url']) }} @else javascript:void(0); @endif">
                    <i class="{{ $menu['icon'] }}"></i>
                    <span>{{ $menu['title'] }}</span>
                    @if(!empty($menu['children']))
                        <span class="fa arrow"></span>
                    @endif
                </a>
                @if(!empty($menu['children']))
                <ul class="nav nav-second-level collapse">
                    @foreach($menu['children'] as $childMenu)
                    <li data-type="left-navigation" @if(get_active_menus($childMenu['active'])) class="active" @endif>
                        <a href="@if(empty($childMenu['children']) && !empty($childMenu['url'])) {{ auto_url($childMenu['url']) }} @else javascript:void(0); @endif">
                            <i class="{{ $childMenu['icon'] }}"></i>
                            {{ $childMenu['title'] }}
                            @if(!empty($childMenu['children']))
                                <span class="fa arrow"></span>
                            @endif
                        </a>
                        @if(!empty($childMenu['children']))
                        <ul class="nav nav-third-level">
                            @foreach($childMenu['children'] as $grandChildMenu)
                            <li data-type="left-navigation" @if(get_active_menus($grandChildMenu['active'])) class="active" @endif>
                                <a href="{{ auto_url($grandChildMenu['url']) }}">
                                    <i class="{{ $grandChildMenu['icon'] }}"></i>
                                    {{ $grandChildMenu['title'] }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif
            </li>
            @endforeach
            @if(session()->get('admin_user_id') == 1)
            <li data-type="left-navigation">
                <a href=" {{ auto_url("system/setting/code/index") }}">
                    <i class="fa fa-code"></i>
                    <span>代码生成器</span>
                </a>
            </li>
            @endif
            {{-- /菜单 --}}
        </ul>
    </div>
</nav>