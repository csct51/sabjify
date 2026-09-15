<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'aoc' => [
        'whatsapp' => [
            'base_url' => env('AOC_WHATSAPP_URL', 'https://api.aoc-portal.com/v1/whatsapp'),
            'key' => env('AOC_WHATSAPP_KEY'),
            'from' => env('AOC_WHATSAPP_FROM', '+918839827923'),
            'campaign' => env('AOC_WHATSAPP_CAMPAIGN', 'api-test'),
            'template' => env('AOC_WHATSAPP_TEMPLATE', 'hello_message'),
            'language' => env('AOC_WHATSAPP_LANGUAGE', 'en'),
        ],
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
