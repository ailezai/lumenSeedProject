<?php


use AiLeZai\Lumen\Framework\Application;

$app->get('/', function () {
    return redirect('login');
});

// 登录页（验证码） / 登录
$app->get('/login', 'LoginController@loginPage');
$app->get('/vCode',  'LoginController@vCode');
$app->post('/login', 'LoginController@login');

// 通过JWT登录
$app->get('/jwt_login','LoginController@jwtLogin');
$app->group([
    'middleware' => ['admin.login']
], function ($app) {
    /**
     * @var Application $app
     */

    // 使用JWT登录其他网站
    $app->get('/jwt_redirect','LoginController@jwtRedirect');
});

// 退出
$app->get('/logout', 'LoginController@logout');

// 忘记密码页 / 发送重置密码请求
$app->get('/forget', 'LoginController@forgetPasswordPage');
$app->post('/forget', 'LoginController@forgetPassword');

// 重置密码页 / 提交重置密码
$app->get('/reset', 'LoginController@resetPasswordPage');
$app->post('/reset', 'LoginController@resetPassword');

// 特殊权限部分
$app->group([
    'middleware' => ['admin.login'],
    'prefix' => 'system'
], function ($app) {
    /**
     * @var Application $app
     */

    // 切换管理员
    $app->get('switch_user', 'LoginController@switchAdminUser');

});

// 权限管理部分
$app->group([
    'middleware' => ['admin.permission'],
    'prefix' => 'system',
    'namespace' => 'Admin',
], function ($app) {
    /**
     * @var Application $app
     */

    // 管理员
    $app->get('user/index',             'AdminUserController@index');
    $app->get('user/add_page',          'AdminUserController@addPage');
    $app->get('user/edit_page',         'AdminUserController@editPage');
    $app->post('user/add_submit',       'AdminUserController@addSubmit');
    $app->post('user/edit_submit',      'AdminUserController@editSubmit');
    $app->get('user/status',            'AdminUserController@status');
    $app->get('user/detail',            'AdminUserController@detail'); // 管理员详情
    $app->get('user/reset_page',        'AdminUserController@resetPage'); // 重置密码页
    $app->post('user/reset',            'AdminUserController@resetSubmit'); // 重置密码提交
    $app->get('user/permission_page',   'AdminUserController@permissionPage'); // 独立授权页
    $app->post('user/permission_submit','AdminUserController@permissionSubmit'); // 独立授权提交
    $app->get('user/refresh_all',       'AdminUserController@refreshAll'); // 刷新所有用户权限

    // 角色
    $app->get('role/index',             'AdminRoleController@index');
    $app->get('role/add_page',          'AdminRoleController@addPage');
    $app->get('role/edit_page',         'AdminRoleController@editPage');
    $app->post('role/add_submit',       'AdminRoleController@addSubmit');
    $app->post('role/edit_submit',      'AdminRoleController@editSubmit');
    $app->get('role/delete',            'AdminRoleController@delete');

    // 权限
    $app->get('permission/index',       'AdminPermissionController@index');
    $app->get('permission/add_page',    'AdminPermissionController@addPage');
    $app->get('permission/edit_page',   'AdminPermissionController@editPage');
    $app->post('permission/add_submit', 'AdminPermissionController@addSubmit');
    $app->post('permission/edit_submit','AdminPermissionController@editSubmit');
    $app->get('permission/delete',      'AdminPermissionController@delete');

    // 菜单
    $app->get('menu/index',             'AdminMenuController@index');
    $app->get('menu/detail',            'AdminMenuController@detail');
    $app->post('menu/order',            'AdminMenuController@order');
    $app->post('menu/submit',           'AdminMenuController@submit');
    $app->get('menu/delete',            'AdminMenuController@delete');

    // 字典表
    $app->get('dictionary/index',       'AdminDictionaryController@index');
    $app->get('dictionary/add_page',    'AdminDictionaryController@addPage');
    $app->get('dictionary/edit_page',   'AdminDictionaryController@editPage');
    $app->post('dictionary/add_submit', 'AdminDictionaryController@addSubmit');
    $app->post('dictionary/edit_submit','AdminDictionaryController@editSubmit');
});

// 日志部分
$app->group([
    'middleware' => ['admin.permission'],
    'prefix' => 'log',
    'namespace' => 'Log',
], function ($app) {
    /**
     * @var Application $app
     */

    // 登录日志
    $app->get('login/index',     'LogLoginController@index');
    // 操作日志
    $app->get('operation/index', 'LogOperationController@index');
    // SQL日志
    $app->get('sql/index',       'LogSqlController@index');
});

// 个人信息部分
$app->group([
    'middleware' => ['admin.login'],
    'prefix' => 'personal',
    'namespace' => 'Personal',
], function ($app) {
    /**
     * @var Application $app
     */

    // 修改密码
    $app->get('password/index',     'PersonalController@passwordIndex');
    $app->post('password/submit',     'PersonalController@passwordSubmit');

    // 个人信息页 / 提交
    $app->get('info/index',     'PersonalController@infoIndex');
    $app->post('info/submit',     'PersonalController@infoSubmit');
});

// 配置
$app->group([
    'middleware' => ['admin.permission'],
    'prefix' => 'system/setting',
    'namespace' => 'Setting',
], function ($app) {
    /**
     * @var Application $app
     */
    // 代码生成
    $app->get('/code/index', 'CodeController@index');
    $app->post('/code/generate', 'CodeController@generate');
});
