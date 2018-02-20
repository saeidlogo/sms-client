<?php

return [
    /*
      |--------------------------------------------------------------------------
      | Default SMS driver
      |--------------------------------------------------------------------------
      |
      | This option controls the default SMS driver user.
      |
      | Supported: "log", "null", "nexmo", "kannel", "kavehnegar"
      |
     */

    'default' => env('SMS_DRIVER', 'kavehnegar'),
    /*
      |--------------------------------------------------------------------------
      | Drivers
      |--------------------------------------------------------------------------
      |
      | Here you can define the settings for each driver.
      |
     */
    'drivers' => [
        'kavehnegar' => [
            'driver' => 'kavehnegar',
            'api_key' => env('KAVEHNEGAR_API_TOKEN', null),
        ],
        'nexmo' => [
            'driver' => 'nexmo',
            'api_key' => env('MY_NEXMO_API_KEY', null),
            'api_secret' => env('MY_NEXMO_API_SECRET', null),
        ],
        'kannel' => [
            'driver' => 'kannel',
            'username' => env('KANNEL_USER_NAME', null),
            'password' => env('KANNEL_PASSWORD', null),
            'url' => 'http://[ip]:13013/cgi-bin/sendsms?username=[username]&password=[password]&from=[from]&to=[to]&text=[text]'
        ],
        'log' => [
            'driver' => 'log',
        ],
        'null' => [
            'driver' => 'null',
        ],
    ],
    'trunks' => [
        'sms01my' => [
            'driver' => 'kannel',
            'cli_support' => false,
            'cli_override' => '+60129899149',
            'url' => 'URL'
        ],
        'smsglobal' => [
            'driver' => 'nexmo',
            'cli_support' => false,
            'cli_override' => '+60129899149',
            'api_key' => 'test',
            'api_secret' => 'test',
            'url' => 'URL'
        ],
        'sms01ir' => [
            'driver' => 'kavehnegar',
            'cli_support' => false,
            'did_provider' => true,
            'cli_ovesender' => '50002001',
            'url' => 'URL'
        ],
        'sms01nl' => [
            'driver' => 'kannel',
            'cli_support' => false,
            'did_provider' => true,
            'cli_ovesender' => '50002001',
            'ip' => '10.1.10.2',
        ]
    ],
    'route_prefix' => [
        '98' => 'sms01ir',
        '60' => 'sms01my',
        '1' => 'smsglobal',
        '2' => 'smsglobal',
        '3' => 'sms01nl',
        '4' => 'smsglobal',
        '5' => 'smsglobal',
        '6' => 'smsglobal',
        '7' => 'smsglobal',
        '8' => 'smsglobal',
        '9' => 'smsglobal',
    ],
    'network_code_prefix' => [
    ]
];
