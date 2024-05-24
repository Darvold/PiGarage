<?php

return [

   'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
/*        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],*/
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\Users::class,
            'table' => 'users',
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\AdminUsers::class,
            'table' => 'admin_users',
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            // 'table' => 'password_reset_tokens',
            // 'expire' => 60,
            // 'throttle' => 60,
        ],
    ],


    'password_timeout' => 10800,

];
