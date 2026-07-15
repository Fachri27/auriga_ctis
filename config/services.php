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

    'turnstile' => [
        // defaults = Cloudflare test keys (always pass) so dev works without setup
        'site_key' => env('TURNSTILE_SITE_KEY', '1x00000000000000000000AA'),
        'secret_key' => env('TURNSTILE_SECRET_KEY', '1x0000000000000000000000000000000AA'),
    ],

    'google_sheets' => [
        'spreadsheet_id' => env('GOOGLE_SPREADSHEET_ID'),
        'range' => env('GOOGLE_SHEETS_RANGE', 'Sheet1!A1:Z'),
        'api_key' => env('GOOGLE_SHEETS_API_KEY'),
        'credentials' => storage_path(env('GOOGLE_SERVICE_ACCOUNT_JSON', 'app/google-service-account.json')),
    ],

    'google_search_console' => [
        // Kode verifikasi meta tag Google Search Console.
        // Render hanya jika diisi (lihat layouts/main.blade.php).
        'verification' => env('GOOGLE_SITE_VERIFICATION'),
    ],

];
