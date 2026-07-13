<?php
// 1. Memaksa Laravel membuang kebiasaan menulis file lokal
putenv('SESSION_DRIVER=cookie');
putenv('LOG_CHANNEL=errorlog');
putenv('CACHE_STORE=array');

// 2. Membuat folder "views" bayangan secara paksa di memori Vercel
if (!file_exists('/tmp/views')) {
    mkdir('/tmp/views', 0777, true);
}

// 3. Memaksa Laravel menggunakan folder bayangan tersebut
putenv('VIEW_COMPILED_PATH=/tmp/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/views';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/views';

// 4. Menjalankan Laravel
require __DIR__ . '/../public/index.php';