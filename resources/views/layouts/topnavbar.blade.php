<div class="row border-bottom">
    <nav class="navbar navbar-static-top white-bg" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="javascript:void(0);">
                <i class="fa fa-bars"></i>
            </a>
        </div>
        <ul class="nav navbar-top-links navbar-right">
            @if(session()->get('system_user') == 1)
            <li>
                <label for="system-switch-admin-username">切换账号</label>
                <input id="system-switch-admin-username" type="text" name="switch_admin_username">
            </li>
            @endif
            <li>
                <a href="{{ auto_url('logout') }}">
                    <i class="fa fa-sign-out"></i>退出登录
                </a>
            </li>
        </ul>
    </nav>
</div>