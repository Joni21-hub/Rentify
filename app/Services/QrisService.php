<?php

namespace App\Services;

/**
 * Class QrisService
 *
 * Layanan Generator QRIS Dinamis Standar Bank Indonesia (ASPI / EMVCo).
 * Mengubah string QRIS Statis (contoh dari DANA Bisnis) menjadi QRIS Dinamis
 * dengan menginjeksi nominal pesanan secara presisi dan menghitung ulang
 * checksum CRC16 (Tag 63).
 */
class QrisService
{
    protected string $baseString;
    protected string $merchantName;
    protected string $nmid;
    protected string $acquirer;

    public function __construct()
    {
        $this->baseString = config('qris.base_string') 
            ?: '00020101021126570011ID.DANA.WWW011893600915300617401802090061740180303UMI51440014ID.CO.QRIS.WWW0215ID10265872193240303UMI5204739453033605802ID5912Rental kursi600416056105316646304065A';
        $this->merchantName = config('qris.merchant_name', 'Rental kursi');
        $this->nmid = config('qris.nmid', 'ID1026587219324');
        $this->acquirer = config('qris.acquirer', 'DANA Bisnis');
    }

    /**
     * Parse payload TLV (Tag-Length-Value) EMVCo menjadi array asosiatif [tag => value].
     */
    public function parse(string $payload): array
    {
        $tags = [];
        $i = 0;
        $len = strlen($payload);

        while ($i < $len) {
            if ($i + 4 > $len) {
                break;
            }
            $tag = substr($payload, $i, 2);
            $length = (int) substr($payload, $i + 2, 2);
            $value = substr($payload, $i + 4, $length);
            $tags[$tag] = $value;
            $i += 4 + $length;
        }

        return $tags;
    }

    /**
     * Hitung checksum CRC-16/CCITT-FALSE standar EMVCo (Polynomial 0x1021, Init 0xFFFF).
     */
    public function crc16(string $data): string
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Validasi apakah string QRIS memiliki CRC16 yang sah.
     */
    public function validateCrc(string $payload): bool
    {
        if (strlen($payload) < 8) {
            return false;
        }
        $dataPart = substr($payload, 0, -4);
        $expectedCrc = substr($payload, -4);
        return strtoupper($this->crc16($dataPart)) === strtoupper($expectedCrc);
    }

    /**
     * Generate QRIS Dinamis dari Base String dengan nominal tertentu.
     *
     * @param int|float $amount Nominal dalam Rupiah (misal 50142)
     * @param string|null $referenceId ID Pesanan / Kode Booking (opsional)
     * @return string String QRIS Dinamis lengkap dengan Tag 01=12, Tag 54=nominal, dan CRC16 baru
     */
    public function generateDynamicQris(int|float $amount, ?string $referenceId = null): string
    {
        $tags = $this->parse($this->baseString);

        // 1. Ubah Point of Initiation Method menjadi Dinamis (12)
        $tags['01'] = '12';

        // 2. Set Tag 54 (Transaction Amount)
        $amountStr = (string) (int) round($amount);
        $tags['54'] = $amountStr;

        // Hapus Tag 63 lama (akan dihitung ulang)
        unset($tags['63']);

        // Urutkan tag secara numerik standar EMVCo
        $orderedKeys = array_keys($tags);
        usort($orderedKeys, function ($a, $b) {
            return (int) $a <=> (int) $b;
        });

        $reconstructed = '';
        foreach ($orderedKeys as $key) {
            $val = $tags[$key];
            $valLen = sprintf('%02d', strlen($val));
            $reconstructed .= $key . $valLen . $val;
        }

        // Tambahkan Tag 63 dengan panjang 04
        $reconstructed .= '6304';
        $checksum = $this->crc16($reconstructed);

        return $reconstructed . $checksum;
    }

    /**
     * Dapatkan URL gambar kode QR beresolusi tinggi.
     */
    public function getQrImageUrl(string $payload, int $size = 350): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&margin=15&data=' . urlencode($payload);
    }

    /**
     * Ambil nama merchant dari tag 59 atau konfigurasi.
     */
    public function getMerchantName(?string $payload = null): string
    {
        if ($payload) {
            $tags = $this->parse($payload);
            if (!empty($tags['59'])) {
                return $tags['59'];
            }
        }
        return $this->merchantName;
    }

    /**
     * Ambil NMID dari tag 51 atau konfigurasi.
     */
    public function getMerchantNmid(?string $payload = null): string
    {
        if ($payload) {
            $tags = $this->parse($payload);
            if (!empty($tags['51'])) {
                // Subtag 02 pada tag 51 berisi NMID 15 digit (contoh: ID1026587219324)
                if (preg_match('/0215(ID\d{13})/', $tags['51'], $matches)) {
                    return $matches[1];
                }
                if (preg_match('/(ID\d+)/', $tags['51'], $matches)) {
                    return substr($matches[1], 0, 15);
                }
            }
        }
        return $this->nmid;
    }

    /**
     * Ambil nama acquirer / penyedia.
     */
    public function getAcquirer(): string
    {
        return $this->acquirer;
    }

    /**
     * Ambil string base QRIS yang aktif.
     */
    public function getBaseString(): string
    {
        return $this->baseString;
    }
}
