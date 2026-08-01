<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Next.js Revalidation Webhook
    |--------------------------------------------------------------------------
    |
    | After an admin mutates talent/portfolio data, RevalidationService POSTs the
    | affected ISR tags to the Next.js app's /api/revalidate route so the
    | frontend regenerates the changed pages. The URL is left blank by default;
    | the service no-ops (and logs) when it is unset — flip it on once the
    | frontend phase ships a matching endpoint.
    |
    */
    'revalidation' => [
        'url' => env('NEXTJS_REVALIDATE_URL'),
        'secret' => env('REVALIDATE_SECRET'),
    ],

];
