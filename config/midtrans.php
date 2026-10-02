<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY') ?: base64_decode('TWlkLXNlcnZlci1IamRaYlZyQ2JUQjZrLXZ4WG5BNFprblA='),
    'client_key' => env('MIDTRANS_CLIENT_KEY') ?: base64_decode('TWlkLWNsaWVudC1lOWtULUdHNnFEYXdQR1Nf'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', true),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];
