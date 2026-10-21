<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Driver
    |--------------------------------------------------------------------------
    |
    | "usms" sends real messages through usms.uz. "log" only writes them to the
    | application log, which is handy for local development and tests.
    |
    */

    'driver' => env('SMS_DRIVER', 'log'),

    'usms' => [
        'base_url' => env('USMS_BASE_URL', 'https://usms.uz/api/v1'),
        'login' => env('USMS_LOGIN', ''),
        'secret' => env('USMS_SECRET', ''),
        // Approved sender name; leave empty to let USMS choose one.
        'from' => (string) env('USMS_FROM', ''),
        // Approved template id per message purpose (see App\Enums\SmsPurpose).
        'templates' => [
            // Variables: {code}. E.g. "SmartPOS tasdiqlash kodi: {code}".
            'registration' => (int) env('USMS_TEMPLATE_ID', 0),
            'password_reset' => (int) env('USMS_TEMPLATE_ID', 0),
            // Variables: {name}, {shop}, {amount}. E.g. "Hurmatli {name}, {shop} do'konidan {amount} so'm qarzingizni to'lash muddati bugun."
            'debt_reminder' => (int) env('USMS_DEBT_TEMPLATE_ID', 0),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification Codes
    |--------------------------------------------------------------------------
    |
    | One-time codes used for registration and password reset. Times are in seconds.
    |
    */

    'verification' => [
        'length' => 6,
        'ttl' => 300,
        'resend_after' => 120,
        'max_attempts' => 5,
    ],

];
