<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'omni' => [
        'api_url' => env('OMNI_API_URL', 'https://omni.quitobici.com/prod-api/iot/api/v2/'),
        'use_proxy' => env('OMNI_USE_PROXY', false),
    ],

    'iot_webhook' => [
        'allowed_ips' => array_filter(
            array_map(
                'trim',
                explode(',', env('IOT_WEBHOOK_ALLOWED_IPS', ''))
            )
        ),
    ],

    'public_test' => [
        'password' => env('PUBLIC_TEST_PASSWORD'),
    ],

];
