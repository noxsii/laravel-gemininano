<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Config
    |--------------------------------------------------------------------------
    */

    'base_url' => env('GEMINI_NANO_URL', 'https://generativelanguage.googleapis.com'),
    'model' => env('GEMINI_NANO_MODEL', 'gemini-2.5-flash-image'),
    'api_key' => env('GEMINI_NANO_KEY'),
    'timeout' => env('GEMINI_NANO_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Image Handling
    |--------------------------------------------------------------------------
    |
    |
    */

    'store' => env('GEMINI_NANO_STORE', true),
    'disk' => env('GEMINI_NANO_DISK', 'public'),
    'path' => env('GEMINI_NANO_PATH', 'gemininano'),

];
