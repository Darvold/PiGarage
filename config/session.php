<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default session "driver" that will be used on
    | requests. By default, we will use the lightweight native driver but
    | you may specify any of the other wonderful drivers provided here.
    |
    | Supported: "file", "cookie", "database", "apc",
    |            "memcached", "redis", "dynamodb", "array", "table"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

/*    'drivers' => [
        'user' => [
            'driver' => 'database',
            'connection' => 'default',
            'table' => 'sessions',
            'lifetime' => 120,
            'expire_on_close' => false,
            'encrypt' => false,
        ],
        'admin' => [
            'driver' => 'database',
            'connection' => 'default',
            'table' => 'admin_sessions',
            'lifetime' => 120,
            'expire_on_close' => false,
            'encrypt' => false,
        ],
    ],*/

    /*'driver' => 'database',*/



    'lifetime' => env('SESSION_LIFETIME', 320),

    'expire_on_close' => false,



    'encrypt' => false,



    'files' => storage_path('framework/sessions'),




    'connection' => env('SESSION_CONNECTION'),



    'table' => env('SESSION_TABLE', 'sessions'),
    'admin_table' => env('ADMIN_SESSION_TABLE', 'admin_sessions'),

    'store' => env('SESSION_STORE'),



    'lottery' => [2, 100],



'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),



    'path' => '/',



    'domain' => env('SESSION_DOMAIN'),



    'secure' => env('SESSION_SECURE_COOKIE'),



    'http_only' => true,


    'same_site' => 'lax',

];
