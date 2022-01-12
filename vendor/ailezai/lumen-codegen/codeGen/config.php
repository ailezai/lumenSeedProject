<?php
return [
    // 相关类若不需要生成，则注释即可
    'model'      => 'app\Models',           // Model路径
    'repository' => 'app\Repositories',     // Repository路径
    'service'    => 'app\Services',         // Service路径
    'controller' => 'app\Http\Controllers', // Controller路径
    'viewType'   => 'NONE',                 // 生成页面类型（NONE：不生成, PJAX：模态框, PAGE：新页面）

    // 当 viewType 不为NONE时有效
    'resourcePath'  => 'temp/blade/',       // view页面文件位置
    'rootRoute'  => 'temp/route',           // 根路由请求
];