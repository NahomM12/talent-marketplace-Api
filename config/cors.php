<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Sanctum SPA cookie auth requires `supports_credentials => true`, which in
    | turn forbids the `*` wildcard origin. CORS_ALLOWED_ORIGINS is a
    | comma-separated list of full origins (with scheme), e.g.
    | "http://localhost:3000,https://app.example.com". Note this differs from
    | SANCTUM_STATEFUL_DOMAINS, which uses bare host:port values.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_map(
        trim(...),
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')),
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
