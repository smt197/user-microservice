<?php
/*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */
if (config('app.env') === 'production') {
    return [
        'paths' => ['api/*', 'broadcasting/auth', 'documents/*'],
        'allowed_methods' => ['*'],
        'allowed_origins' => ['https://app.resurex.org'],
        'allowed_origins_patterns' => [],
        'allowed_headers' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => true,
    ];
}

return [
    'paths' => ['api/*', 'broadcasting/auth', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://backend.192.168.1.10.sslip.io:8001',
        'http://user.192.168.1.10.sslip.io:8003',
        'http://backend.192.168.1.10.sslip.io',
        'http://user.192.168.1.10.sslip.io',
        '192.168.1.10.sslip.io'
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
