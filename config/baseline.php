<?php

return [
    'features' => [
        'admin' => (bool) env('BASELINE_ADMIN', true),
        'billing' => (bool) env('BASELINE_BILLING', true),
        'locales' => (bool) env('BASELINE_LOCALES', true),
    ],
];
