<?php

return [
    App\Providers\AppServiceProvider::class,
    CloudinaryLabs\CloudinaryLaravel\CloudinaryServiceProvider::class, // <-- INI YANG WAJIB DITAMBAHKAN AGAR MENYALA DI VERCEL
];