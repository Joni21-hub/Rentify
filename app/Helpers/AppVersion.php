<?php

namespace App\Helpers;

class AppVersion
{
    /**
     * Mendapatkan string versi aplikasi saat ini.
     * Menggabungkan versi dasar dengan timestamp modifikasi berkas-berkas inti
     * sehingga setiap kali kode, view Blade, atau aset di-update di server/git,
     * versi aplikasi otomatis berubah tanpa perlu diubah manual.
     */
    public static function get(): string
    {
        $baseVersion = config('app.version', '1.14.0');
        $mtime = self::timestamp();

        return $baseVersion . '.' . ($mtime > 0 ? date('YmdHi', $mtime) : 'latest');
    }

    /**
     * Timestamp modifikasi terakhir dari berkas penting.
     */
    public static function timestamp(): int
    {
        $filesToCheck = [
            base_path('routes/web.php'),
            public_path('sw.js'),
            public_path('rentify-theme.css'),
            resource_path('views/customer/home.blade.php'),
            resource_path('views/layouts/app.blade.php'),
        ];

        $latestMtime = 0;
        foreach ($filesToCheck as $file) {
            if (file_exists($file)) {
                $m = filemtime($file);
                if ($m > $latestMtime) {
                    $latestMtime = $m;
                }
            }
        }

        return $latestMtime ?: time();
    }
}
