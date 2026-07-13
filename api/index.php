<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Panggil autoloader bawaan
require __DIR__.'/../vendor/autoload.php';

// 2. Bentuk aplikasi Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

// 3. KUNCI PENYELAMAT VERCEL: Paksa pindah ke /tmp SEBELUM aplikasi dinyalakan!
if (isset($_ENV['VERCEL'])) {
    $storagePath = '/tmp/storage';
    $app->useStoragePath($storagePath);

    foreach (['/framework/views', '/framework/cache/data', '/framework/sessions', '/logs'] as $folder) {
        if (!is_dir($storagePath . $folder)) {
            mkdir($storagePath . $folder, 0755, true);
        }
    }
}

// 4. Jalankan aplikasi (Sekarang semua folder view 100% sudah aman & terbuka!)
$app->handleRequest(Request::capture());