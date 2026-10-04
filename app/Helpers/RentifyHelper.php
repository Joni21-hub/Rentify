<?php

namespace App\Helpers;

class RentifyHelper
{
    /**
     * Mempersingkat alamat menjadi nama desa / gampong / area saja
     * Contoh: "First, Jalan Tgk. Abdul Wahab Dahlawy, Reuleut Timu, Sawang, Aceh Utara, Aceh, 23442, Indonesia"
     * Menjadi: "Reuleut Timu"
     */
    public static function formatAreaDesa(?string $alamat): string
    {
        if (empty($alamat)) {
            return 'Area belum diatur';
        }

        $alamat = trim($alamat);
        $parts = array_map('trim', explode(',', $alamat));

        if (count($parts) <= 1) {
            return $alamat;
        }

        // Daftar filter pola yang bukan desa (provinsi, kabupaten, kode pos, negara, jalan, dsb)
        $ignorePatterns = [
            '/^indonesia$/i',
            '/^\d{4,6}$/', // kode pos
            '/^(provinsi\s+|prov\.\s+)?(aceh|sumatera|jawa|bali|kalimantan|sulawesi|papua)/i',
            '/^(kabupaten|kab\.|kota)\b/i',
            '/^(north\s+aceh|aceh\s+utara|lhokseumawe|bireuen|pidie|banda\s+aceh)/i',
            '/^(jalan|jl\.|gang|gg\.|lorong|lr\.|dusun|dsn\.)\b/i',
            '/^(first|second|third|floor|lt\.|gedung|building|universitas|univ\.|kampus)\b/i',
        ];

        $filtered = [];
        foreach ($parts as $part) {
            $shouldIgnore = false;
            foreach ($ignorePatterns as $pattern) {
                if (preg_match($pattern, $part)) {
                    $shouldIgnore = true;
                    break;
                }
            }
            if (!$shouldIgnore && mb_strlen($part) > 1) {
                $filtered[] = $part;
            }
        }

        // Ambil kandidat pertama yang merupakan nama desa/area
        if (!empty($filtered)) {
            return $filtered[0];
        }

        // Fallback jika semua terfilter
        return $parts[min(2, count($parts) - 1)];
    }
}
