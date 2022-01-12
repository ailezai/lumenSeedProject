<?php

use AiLeZai\Lumen\Framework\Application;

$app->group([
    'middleware' => ['admin.login']
], function ($app) {
    /**
     * @var Application $app
     */
    $app->get('/index', 'IndexController@index');

});

// 报表部分
$app->group([
    'middleware' => ['admin.login'],
    'prefix' => 'stats',
    'namespace' => 'Stats',
], function ($app) {
    /**
     * @var Application $app
     */

    //
    $app->get('eggs/index',     'StatsEggsController@index');
});

// 消息部分
$app->group([
    'middleware' => ['admin.login'],
    'prefix' => 'message',
    'namespace' => 'Message',
], function ($app) {
    /**
     * @var Application $app
     */

    //
    $app->get('miniapp/index',     'MiniappController@index');
    $app->get('miniapp/add_page',  'MiniappController@addPage');
    $app->post('miniapp/add_submit',       'MiniappController@addSubmit');
    $app->get('miniapp/send_msg',     'MiniappController@sendMsg');
    $app->get('miniapp/del',     'MiniappController@del');

});