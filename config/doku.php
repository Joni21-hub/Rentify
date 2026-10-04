<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DOKU Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi DOKU Checkout (Jokul) API.
    | Mendukung mode sandbox dan production.
    |
    */

    'client_id'   => env('DOKU_CLIENT_ID', ''),
    'secret_key'  => env('DOKU_SECRET_KEY', ''),
    'api_key'     => env('DOKU_API_KEY', ''),
    'environment' => env('DOKU_ENVIRONMENT', 'sandbox'),
    'base_url'    => env('DOKU_BASE_URL', 'https://api-sandbox.doku.com'),

    /*
    |--------------------------------------------------------------------------
    | Payment Expiry
    |--------------------------------------------------------------------------
    | Durasi kedaluwarsa tagihan pembayaran (dalam menit).
    | Default: 30 menit (ala Shopee).
    |
    */
    'expiry_minutes' => (int) env('DOKU_EXPIRY_MINUTES', 30),
];
