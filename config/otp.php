<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP driver
    |--------------------------------------------------------------------------
    |
    | log       — local/dev: fixed code (OTP_DEV_CODE), no SMS
    | twofactor  — 2factor.in AUTOGEN + session VERIFY (production for Tan Tan app)
    |
    */

    'driver' => env('OTP_DRIVER', 'twofactor'),

    'dev_code' => env('OTP_DEV_CODE', '123456'),

    'length' => 6,

    'ttl_seconds' => (int) env('OTP_TTL_SECONDS', 300),

    'twofactor' => [
        'api_key' => env('TWOFACTOR_API_KEY'),
        'template' => env('TWOFACTOR_SMS_TEMPLATE', 'OTP1'),
        'base_url' => rtrim(env('TWOFACTOR_BASE_URL', 'https://2factor.in/API/V1'), '/'),
        'timeout' => (int) env('TWOFACTOR_TIMEOUT', 15),
    ],

];
