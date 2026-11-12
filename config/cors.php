<?php

$origenes = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
)));

if ($origenes === []) {
    $local = env('APP_ENV') === 'local';

    $origenes = array_values(array_filter([
        env('APP_URL', 'http://localhost'),
        $local ? 'http://localhost' : null,
        $local ? 'http://localhost:8000' : null,
        $local ? 'http://127.0.0.1:8000' : null,
        $local ? 'http://localhost:5173' : null,
        $local ? 'http://127.0.0.1:5173' : null,
    ]));
}

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_unique($origenes)),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'Origin', 'X-Requested-With', 'X-CSRF-TOKEN', 'X-XSRF-TOKEN'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];
