<?php

$jwtPublicKey = [
    'loc' => "",
    'dev' => "",
    'pre' => "",
    'pdt' => "",
];

return [

    'mysql' => [
        env('MYSQL_NAME') => [
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'user'     => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset'  => env('DB_CHARSET'),
            'database' => env('DB_DATABASE'),
        ],
        env('MYSQL_CLUSTER_NAME') => [
            'host'     => env('DB_CLUSTER_HOST'),
            'port'     => env('DB_CLUSTER_PORT'),
            'user'     => env('DB_CLUSTER_USERNAME'),
            'password' => env('DB_CLUSTER_PASSWORD'),
            'charset'  => env('DB_CLUSTER_CHARSET'),
            'database' => env('DB_CLUSTER_DATABASE'),
        ]
    ],

    'redis' => [
        env('REDIS_NAME') => [
            'host'  => env('REDIS_HOST'),
            'port'  => env('REDIS_PORT'),
            'index' => env('REDIS_DATABASE'),
            'auth'  => env('REDIS_PASSWORD'),
        ],
        env('REDIS_CLUSTER_NAME') => [
            'host'  => env('REDIS_CLUSTER_HOST'),
            'port'  => env('REDIS_CLUSTER_PORT'),
            'index' => env('REDIS_CLUSTER_DATABASE'),
            'auth'  => env('REDIS_CLUSTER_PASSWORD'),
        ]
    ],

    'msgq' => [
        env('MSGQ_NAME') => [
            'host'     => env('MSGQ_HOST'),
            'port'     => env('MSGQ_PORT'),
            'user'     => env('MSGQ_USER'),
            'password' => env('MSGQ_PASSWORD'),
            'vhost'    => env('MSGQ_VHOST'),
        ],
        env('MSGQ_CLUSTER_NAME') => [
            'host'     => env('MSGQ_CLUSTER_HOST'),
            'port'     => env('MSGQ_CLUSTER_PORT'),
            'user'     => env('MSGQ_CLUSTER_USER'),
            'password' => env('MSGQ_CLUSTER_PASSWORD'),
            'vhost'    => env('MSGQ_CLUSTER_VHOST'),
        ]
    ],

    'log' => [
        'category'       => env('LOG_CATEGORY'),
        'file_base_name' => env('LOG_FILE_BASE_NAME')
    ]
];