<?php

return [
    'available' => ['en', 'nl'],
    'labels' => [
        'en' => 'English',
        'nl' => 'Nederlands',
    ],
    'cookie_minutes' => (int) env('LOCALE_COOKIE_MINUTES', 60 * 24 * 365),
];
