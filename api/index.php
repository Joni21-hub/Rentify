<?php

// 1. Deteksi Vercel: Siapkan semua folder sementara SEBELUM Laravel dinyalakan!
if (isset($_ENV['VERCEL'])) {
    $storagePath = '/tmp/storage';

    // Buat folder utama dan sub-folder yang wajib ada di Laravel
    foreach (['/framework/views', '/framework/cache/data', '/framework/sessions', '/logs', '/app/public'] as $folder) {
        if (!is_dir($storagePath . $folder)) {
            mkdir($storagePath . $folder, 0755, true);
        }
    }

    // Paksa Laravel memakai folder /tmp dari detik pertama pintu dibuka
    putenv('VIEW_COMPILED_PATH=' . $storagePath . '/framework/views');
    $_ENV['VIEW_COMPILED_PATH'] = $storagePath . '/framework/views';
}

// 2. Buka aplikasi Laravel seperti biasa
require __DIR__ . '/../public/index.php';