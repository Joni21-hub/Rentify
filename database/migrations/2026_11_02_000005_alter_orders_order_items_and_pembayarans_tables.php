<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Alterasi tabel orders (pesanan / Penyewaan)
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_group_id', 64)->nullable()->after('kode_booking');
            $table->index('payment_group_id', 'idx_orders_payment_group');
            $table->index('payment_group_id', 'idx_penyewaans_payment_group');

            $table->foreignId('voucher_toko_id')
                ->nullable()
                ->after('voucher_id')
                ->constrained('vouchers')
                ->onDelete('set null');

            $table->decimal('potongan_voucher_toko', 12, 2)->default(0.00)->after('voucher_toko_id');

            $table->foreignId('voucher_platform_id')
                ->nullable()
                ->after('potongan_voucher_toko')
                ->constrained('vouchers')
                ->onDelete('set null');

            $table->decimal('potongan_voucher_platform', 12, 2)->default(0.00)->after('voucher_platform_id');
        });

        // Backfill data payment_group_id untuk pesanan yang sudah ada menggunakan kode_booking
        DB::statement("UPDATE orders SET payment_group_id = kode_booking WHERE payment_group_id IS NULL");

        // 2. Alterasi tabel order_items (item pesanan / DetailPenyewaan)
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('subtotal_sewa_asli', 12, 2)->default(0.00)->after('quantity');
            $table->decimal('subtotal_sewa_markup', 12, 2)->default(0.00)->after('subtotal_sewa_asli');
            $table->decimal('alokasi_diskon_toko', 12, 2)->default(0.00)->after('subtotal_sewa_markup');
            $table->decimal('alokasi_diskon_platform', 12, 2)->default(0.00)->after('alokasi_diskon_toko');
            $table->decimal('alokasi_diskon_koin', 12, 2)->default(0.00)->after('alokasi_diskon_platform');
            $table->decimal('subtotal_sewa_bersih', 12, 2)->default(0.00)->after('alokasi_diskon_koin');
            $table->enum('status_item', ['aktif', 'dibatalkan', 'selesai'])->default('aktif')->after('subtotal_sewa_bersih');
        });

        // 3. Alterasi tabel pembayarans (multi-vendor linkage)
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('payment_group_id', 64)->nullable()->after('order_id');
            $table->index('payment_group_id', 'idx_pembayarans_payment_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropIndex('idx_pembayarans_payment_group');
            $table->dropColumn('payment_group_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal_sewa_asli',
                'subtotal_sewa_markup',
                'alokasi_diskon_toko',
                'alokasi_diskon_platform',
                'alokasi_diskon_koin',
                'subtotal_sewa_bersih',
                'status_item',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['voucher_toko_id']);
            $table->dropForeign(['voucher_platform_id']);
            $table->dropIndex('idx_orders_payment_group');
            $table->dropIndex('idx_penyewaans_payment_group');
            $table->dropColumn([
                'voucher_toko_id',
                'potongan_voucher_toko',
                'voucher_platform_id',
                'potongan_voucher_platform',
                'payment_group_id',
            ]);
        });
    }
};
