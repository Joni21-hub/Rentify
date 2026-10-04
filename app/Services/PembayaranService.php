<?php

namespace App\Services;

/**
 * Interface kontrak untuk driver pembayaran.
 * Semua implementasi gateway pembayaran harus mengimplementasikan interface ini.
 *
 * @deprecated Gunakan DokuService secara langsung. Interface ini disimpan untuk
 *             kompatibilitas ke depan jika diperlukan multi-driver.
 */
interface PembayaranDriverInterface
{
    public function createTransaction(array $data): array;
    public function verifyPayment(string $transactionId): bool;
    public function refund(string $transactionId, float $amount): bool;
}

/**
 * Driver pembayaran manual (admin verifikasi manual).
 * Dapat digunakan sebagai fallback atau untuk transaksi COD.
 */
class ManualPaymentDriver implements PembayaranDriverInterface
{
    public function createTransaction(array $data): array
    {
        return ['status' => 'pending', 'message' => 'Menunggu verifikasi admin.'];
    }

    public function verifyPayment(string $transactionId): bool
    {
        return true; // Admin meverifikasi secara manual
    }

    public function refund(string $transactionId, float $amount): bool
    {
        return true; // Proses refund manual oleh admin
    }
}

// Driver aktif: DokuService (lihat app/Services/DokuService.php)
// Untuk masa depan: class XenditDriver implements PembayaranDriverInterface { ... }