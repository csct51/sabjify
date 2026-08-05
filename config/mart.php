<?php

return [
    'currency' => '₹',

    'delivery_fee' => env('MART_DELIVERY_FEE', 40),

    'free_delivery_threshold' => env('MART_FREE_DELIVERY_THRESHOLD', 499),

    'placeholder_image' => env('MART_PLACEHOLDER_IMAGE', 'https://placehold.co/600x600/F0FDF4/166534'),

    'payment_methods' => [
        'cod' => [
            'label' => 'Cash on Delivery',
            'description' => 'Pay in cash when your order arrives',
            'icon' => 'banknote',
        ],
        'online' => [
            'label' => 'Pay Online',
            'description' => 'UPI, Cards, Net Banking',
            'icon' => 'credit-card',
        ],
    ],

    'enabled_payment_methods' => ['cod', 'online'],
];
