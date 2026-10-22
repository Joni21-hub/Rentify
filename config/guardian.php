<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rentify AI Server Guardian Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi agen AI pengawas, monitoring sistem otonom 24/7,
    | dan antarmuka komando WhatsApp untuk Pemilik (Owner / Super Admin).
    |
    */

    'enabled' => env('RENTIFY_GUARDIAN_ENABLED', true),

    // URL website target monitoring
    'target_url' => env('RENTIFY_TARGET_URL', 'https://rentify21.my.id'),

    // Nomor WhatsApp Pemilik (Boss Joni Utama)
    'owner_phones' => array_values(array_filter(array_map('trim', explode(',', env('GUARDIAN_OWNER_PHONES', '083183494835,082183394549'))))),

    // Thresholds peringatan otomatis
    'thresholds' => [
        'latency_warning_ms'   => (int) env('GUARDIAN_LATENCY_WARN_MS', 3000),
        'latency_critical_ms'  => (int) env('GUARDIAN_LATENCY_CRIT_MS', 6000),
        'db_latency_crit_ms'   => (int) env('GUARDIAN_DB_LATENCY_CRIT_MS', 2500),
        'disk_warning_percent' => (int) env('GUARDIAN_DISK_WARN_PERCENT', 88),
        'disk_critical_percent'=> (int) env('GUARDIAN_DISK_CRIT_PERCENT', 95),
        'recent_errors_crit'   => (int) env('GUARDIAN_ERRORS_CRIT_COUNT', 5), // Error baru dalam 15 menit
        'alert_cooldown_min'   => (int) env('GUARDIAN_ALERT_COOLDOWN_MIN', 20), // Waktu jeda alert duplikat (menit)
    ],

    // Konfigurasi Google Gemini untuk Rentify Guardian
    'gemini' => [
        'model'       => env('GUARDIAN_GEMINI_MODEL', 'gemini-1.5-flash'),
        'temperature' => (float) env('GUARDIAN_GEMINI_TEMP', 0.2), // Rendah untuk presisi teknis & singkat
        'max_tokens'  => (int) env('GUARDIAN_GEMINI_MAX_TOKENS', 350),
    ],
];
