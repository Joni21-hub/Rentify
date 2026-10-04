<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom DOKU payment ke tabel orders dan pembayarans.
 *
 * Kolom orders baru:
 *   - doku_invoice_number : nomor invoice yang dikirim ke DOKU (= kode_booking)
 *   - doku_payment_url    : URL halaman pembayaran DOKU yang dibuka customer
 *   - doku_payment_id     : ID transaksi dari DOKU (dari webhook)
 *   - payment_expired_at  : waktu kedaluwarsa pembayaran (30 menit dari order)
 *   - paid_at             : waktu sukses pembayaran dikonfirmasi
 *
 * Kolom pembayarans baru:
 *   - doku_payment_id     : ID transaksi dari DOKU
 *   - doku_channel        : channel pembayaran yang dipakai (QRIS, VA BCA, dll)
 *   - paid_at             : waktu pembayaran sukses
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Kolom DOKU di tabel orders ────────────────────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'doku_invoice_number')) {
                $table->string('doku_invoice_number')->nullable()->after('kode_booking')
                      ->comment('Nomor invoice yang dikirim ke DOKU Checkout');
            }
            if (!Schema::hasColumn('orders', 'doku_payment_url')) {
                $table->text('doku_payment_url')->nullable()->after('doku_invoice_number')
                      ->comment('URL halaman pembayaran DOKU');
            }
            if (!Schema::hasColumn('orders', 'doku_payment_id')) {
                $table->string('doku_payment_id')->nullable()->after('doku_payment_url')
                      ->comment('ID transaksi dari DOKU (dari webhook notification)');
            }
            if (!Schema::hasColumn('orders', 'payment_expired_at')) {
                $table->timestamp('payment_expired_at')->nullable()->after('doku_payment_id')
                      ->comment('Batas waktu pembayaran (otomatis EXPIRED jika melewati ini)');
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_expired_at')
                      ->comment('Waktu konfirmasi pembayaran sukses dari DOKU');
            }
        });

        // ── Kolom DOKU di tabel pembayarans ──────────────────────────────────
        Schema::table('pembayarans', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayarans', 'doku_payment_id')) {
                $table->string('doku_payment_id')->nullable()->after('metode')
                      ->comment('ID transaksi DOKU');
            }
            if (!Schema::hasColumn('pembayarans', 'doku_channel')) {
                $table->string('doku_channel')->nullable()->after('doku_payment_id')
                      ->comment('Kanal pembayaran: QRIS, VIRTUAL_ACCOUNT_BCA, dll');
            }
            if (!Schema::hasColumn('pembayarans', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('doku_channel')
                      ->comment('Waktu pembayaran dikonfirmasi sukses');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = ['doku_invoice_number', 'doku_payment_url', 'doku_payment_id', 'payment_expired_at', 'paid_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $cols = ['doku_payment_id', 'doku_channel', 'paid_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('pembayarans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
