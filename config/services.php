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

    'whatsapp' => [
        'node_url' => env('WA_SERVICE_URL', env('WA_NODE_URL')),
        'token' => env('WA_SHARED_KEY', env('WA_SERVICE_TOKEN', env('WA_NODE_TOKEN'))),
        'context_key' => env('WA_CONTEXT_KEY', env('WA_SHARED_KEY', env('WA_SERVICE_TOKEN', env('WA_NODE_TOKEN')))),
        'default_country' => env('WA_DEFAULT_COUNTRY', '62'),
        'sender_name' => env('WA_SENDER_NAME', 'SITUBA'),
    ],

];
