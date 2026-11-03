<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rentify Application Download URL (Direct APK / App Store)
    |--------------------------------------------------------------------------
    |
    | Jika diisi tautan file APK atau landing page download eksternal, tombol
    | 'Download Aplikasi' akan mengarahkan pengguna ke tautan ini. Jika null/kosong,
    | sistem secara otomatis memicu instalasi Progressive Web App (PWA).
    |
    */
    'app_download_url' => env('APP_DOWNLOAD_URL', null),
];
