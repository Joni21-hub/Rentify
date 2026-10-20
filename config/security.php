<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rentify Autonomous Security Defense & Alert Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automated threat detection, rate limiting, IP blocking,
    | and real-time WhatsApp alert dispatching to the system administrator.
    |
    */

    // Nomor WhatsApp Admin yang menerima alarm darurat keamanan via Fonnte
    'admin_phone' => env('ADMIN_SECURITY_PHONE', '083183494835'),

    'firewall' => [
        // Aktifkan atau nonaktifkan firewall middleware
        'enabled' => env('SECURITY_FIREWALL_ENABLED', true),

        // Durasi pemblokiran IP mencurigakan (dalam menit)
        'block_duration_minutes' => env('SECURITY_BLOCK_MINUTES', 30),

        // Waktu jeda cooldown pengiriman alert WA agar tidak spam/menghabiskan kuota Fonnte
        'alert_cooldown_minutes' => env('SECURITY_ALERT_COOLDOWN_MINUTES', 10),
    ],

    'rate_limits' => [
        // Batas percobaan login gagal sebelum akun/IP dikunci sementara
        'login_max_attempts' => 5,
        'login_decay_minutes' => 15,

        // Batas percobaan verifikasi OTP gagal
        'otp_verify_max_attempts' => 5,
        'otp_verify_decay_minutes' => 15,

        // Batas permintaan kirim ulang OTP (Anti-spam / Anti-bombing)
        'otp_resend_max_attempts' => 3,
        'otp_resend_decay_minutes' => 10,

        // Batas permohonan reset password (Lupa Password)
        'forgot_password_max_attempts' => 3,
        'forgot_password_decay_minutes' => 10,
    ],
];
