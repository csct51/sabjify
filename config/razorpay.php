<?php

return [
    'key_id' => env('RAZORPAY_KEY_ID', ''),

    'key_secret' => env('RAZORPAY_KEY_SECRET', ''),

    'name' => env('RAZORPAY_NAME', env('APP_NAME', 'Mart')),

    'description' => env('RAZORPAY_DESCRIPTION', 'Order payment'),

    'theme_color' => env('RAZORPAY_THEME_COLOR', '#16a34a'),
];
