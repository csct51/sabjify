<?php

return [
    'currency' => '₹',

    'delivery_fee' => env('MART_DELIVERY_FEE', 40),

    'free_delivery_threshold' => env('MART_FREE_DELIVERY_THRESHOLD', 499),

    'minimum_order_amount' => env('MART_MINIMUM_ORDER_AMOUNT', 0),

    'placeholder_image' => env('MART_PLACEHOLDER_IMAGE', 'https://placehold.co/600x600/F0FDF4/166534'),

    'payment_methods' => [
        'cod' => [
            'label' => 'Cash on Delivery',
            'description' => 'Pay in cash when your order arrives',
            'icon' => 'banknote',
        ],
        'online' => [
            'label' => 'Pay Online',
            'description' => 'Pay securely via Razorpay (UPI, Cards, Net Banking)',
            'icon' => 'credit-card',
        ],
    ],

    'enabled_payment_methods' => ['cod', 'online'],

    'nominatim_base_url' => env('NOMINATIM_BASE_URL', 'https://nominatim.openstreetmap.org'),

    'map_default_lat' => env('MART_MAP_DEFAULT_LAT', 21.2514),

    'map_default_lng' => env('MART_MAP_DEFAULT_LNG', 81.6296),
];
