<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto-Detect Mutasi Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi webhook penerima forward notifikasi Android (MacroDroid,
    | Notification Forwarder, Webhook Notifier, dll.) untuk verifikasi
    | mutasi QRIS / Bank otomatis tanpa sentuh tombol 24/7.
    |
    */

    // Secret key untuk verifikasi keaslian webhook dari HP Android
    'webhook_secret' => env('MUTASI_WEBHOOK_SECRET', 'rentify_mutasi_secret_2026'),

    // Aktifkan kode unik 3-digit (100-999) pada saat checkout QRIS
    // Menjamin probabilitas collision mendekati 0% saat banyak transaksi bersamaan
    'enable_kode_unik' => env('MUTASI_ENABLE_KODE_UNIK', true),

    // Batas waktu toleransi pencocokan order (dalam menit)
    'match_window_minutes' => (int) env('MUTASI_MATCH_WINDOW_MINUTES', 1440), // 24 jam

    // Kirim notifikasi WhatsApp otomatis saat mutasi cocok & lunas
    'send_wa_notification' => env('MUTASI_SEND_WA_NOTIFICATION', true),
];
