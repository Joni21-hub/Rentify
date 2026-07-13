<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Panggil autoloader bawaan
require __DIR__.'/../vendor/autoload.php';

// 2. Bentuk aplikasi Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

// 3. KUNCI SERVERLESS VERCEL: Gunakan /tmp untuk storage DAN bootstrap cache
// Ini mematikan total jebakan file cache dari server build Vercel!
if (isset($_ENV['VERCEL'])) {
    $storagePath = '/tmp/storage';
    $bootstrapPath = '/tmp/bootstrap';

    $app->useStoragePath($storagePath);
    $app->useBootstrapPath($bootstrapPath);

    foreach (['/framework/views', '/framework/cache/data', '/framework/sessions', '/logs'] as $folder) {
        if (!is_dir($storagePath . $folder)) {
            mkdir($storagePath . $folder, 0755, true);
        }
    }
    if (!is_dir($bootstrapPath)) {
        mkdir($bootstrapPath, 0755, true);
    }
}

// 4. TANGKAP ERROR ASLI: Jangan biarkan Laravel menyembunyikan error di balik resolve('view')
try {
    $request = Request::capture();
    // Paksa Laravel menjawab dengan format JSON jika terjadi error sistem internal
    $request->headers->set('Accept', 'application/json');
    
    $response = $app->handleRequest($request);
    $response->send();
    $app->terminate();
} catch (\Throwable $e) {
    // Jika ada error fatal dari dalam sistem, langsung cetak ke layar browser Anda tanpa tebak-tebakan!
    header('Content-Type: text/html; charset=utf-8');
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #fff3cd; border: 2px solid #ffeeba; border-radius: 8px; margin: 20px;'>";
    echo "<h2 style='color: #856404; margin-top:0;'>⚠️ AKAR MASALAH ASLI DITEMUKAN:</h2>";
    echo "<p><strong>Pesan Error:</strong> <span style='color: #d9534f; font-size: 18px; font-weight: bold;'>" . htmlspecialchars($e->getMessage()) . "</span></p>";
    echo "<p><strong>Terjadi di File:</strong> <code>" . htmlspecialchars($e->getFile()) . "</code> (Baris ke-" . $e->getLine() . ")</p>";
    echo "<hr style='border: 0; border-top: 1px solid #ccc; margin: 15px 0;'>";
    echo "<p style='font-size: 14px; color: #666;'><strong>Detail Pelacakan (Stack Trace):</strong></p>";
    echo "<pre style='background: #222; color: #0f0; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
    exit;
}