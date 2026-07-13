<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Panggil autoloader bawaan
require __DIR__.'/../vendor/autoload.php';

// 2. Bentuk aplikasi Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

// 3. KUNCI SERVERLESS VERCEL: Alihkan storage & cache ke /tmp TANPA merusak jalur bootstrap!
if (isset($_ENV['VERCEL'])) {
    $storagePath = '/tmp/storage';
    $cachePath = '/tmp/cache';

    $app->useStoragePath($storagePath);

    foreach (['/framework/views', '/framework/cache/data', '/framework/sessions', '/logs'] as $folder) {
        if (!is_dir($storagePath . $folder)) {
            mkdir($storagePath . $folder, 0755, true);
        }
    }
    if (!is_dir($cachePath)) {
        mkdir($cachePath, 0755, true);
    }

    foreach ([
        'APP_SERVICES_CACHE' => $cachePath . '/services.php',
        'APP_PACKAGES_CACHE' => $cachePath . '/packages.php',
        'APP_CONFIG_CACHE' => $cachePath . '/config.php',
        'APP_ROUTES_CACHE' => $cachePath . '/routes.php',
        'APP_EVENTS_CACHE' => $cachePath . '/events.php',
    ] as $key => $path) {
        $_ENV[$key] = $path;
        $_SERVER[$key] = $path;
        putenv("{$key}={$path}");
    }
}

// 4. Jalankan aplikasi standar Laravel 11 dengan Penangkap Error
try {
    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(500);
    }
    
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #fff3cd; border: 2px solid #ffeeba; border-radius: 8px; margin: 20px; position: relative; z-index: 999999;'>";
    echo "<h2 style='color: #856404; margin-top:0;'>⚠️ DIAGNOSIS ERROR LAPIS DEMI LAPIS:</h2>";
    
    $currentException = $e;
    $step = 1;
    while ($currentException) {
        echo "<div style='background: #fff; padding: 15px; border-left: 4px solid #d9534f; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);'>";
        echo "<h3 style='color: #d9534f; margin: 0 0 5px 0;'>Lapis {$step}: " . htmlspecialchars(get_class($currentException)) . "</h3>";
        echo "<p style='font-size: 16px; font-weight: bold; color: #333; margin: 5px 0;'>" . htmlspecialchars($currentException->getMessage()) . "</p>";
        echo "<p style='font-size: 13px; color: #666; margin: 0;'><strong>Lokasi:</strong> <code>" . htmlspecialchars($currentException->getFile()) . "</code> (Baris ke-" . $currentException->getLine() . ")</p>";
        echo "</div>";
        
        $currentException = $currentException->getPrevious();
        $step++;
    }
    
    echo "</div>";
    exit;
}