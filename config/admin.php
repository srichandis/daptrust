<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin credentials
    |--------------------------------------------------------------------------
    |
    | Session-based credentials used to protect the /admin area.
    | Override via ADMIN_USERNAME / ADMIN_PASSWORD in .env.
    |
    */

    'username' => env('ADMIN_USERNAME', 'admin'),
    'password' => env('ADMIN_PASSWORD', 'password'),

    /*
    |--------------------------------------------------------------------------
    | Password recovery (security question)
    |--------------------------------------------------------------------------
    |
    | Used by the forgot-password flow. Override via ADMIN_SECURITY_QUESTION /
    | ADMIN_SECURITY_ANSWER in .env. Leave the answer empty to disable it.
    |
    */

    'security_question' => env('ADMIN_SECURITY_QUESTION', 'What is the trust name?'),
    'security_answer' => env('ADMIN_SECURITY_ANSWER', ''),

    /*
    |--------------------------------------------------------------------------
    | Trust name
    |--------------------------------------------------------------------------
    |
    | Displayed in the admin panel header.
    |
    */

    'trust_name' => env('TRUST_NAME', 'DAP Trust'),

];
