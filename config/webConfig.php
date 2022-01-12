<?php

return [

    'view' => [

        'title' => '管理系统',

        'footer' => 'AiLeZai管理系统v1.0.1',

        'copyright' => 'AiLeZai &copy; 2014-'. ((($Y = intval(date('Y'))) > 2014) ? "$Y" : ''),

        'mini_logo' => 'Jll',

        'login' => [

            'logo' => 'MANAGEMENT',

            'title' => '欢迎使用管理系统'
        ]
    ],

    // 分页大小
    'paginate' => [
        'x-small'   => 3,
        'small'     => 5,
        'middle'    => 10,
        'large'     => 15,
        'larger'    => 20,
        'x-large'   => 30,
        'xx-large'  => 50,
        'xxx-large' => 100,
    ]
];