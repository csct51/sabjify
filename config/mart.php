<?php

return [
    'currency' => '₹',

    'delivery_fee' => env('MART_DELIVERY_FEE', 40),

    'free_delivery_threshold' => env('MART_FREE_DELIVERY_THRESHOLD', 499),

    'placeholder_image' => env('MART_PLACEHOLDER_IMAGE', 'https://placehold.co/600x600/F0FDF4/166534'),
];
