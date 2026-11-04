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
        if (!Schema::hasTable('order_voucher_redemptions')) {
            Schema::create('order_voucher_redemptions', function (Blueprint $table) {
                $table->id();
                $table->string('payment_group_id', 64)->index('idx_redemptions_payment_group');
                $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
                $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('restrict');
                $table->foreignId('customer_voucher_id')->nullable()->constrained('customer_vouchers')->onDelete('set null');
                $table->enum('tipe_voucher', ['toko', 'platform']);
                $table->decimal('alokasi_diskon', 12, 2);
                $table->enum('status', ['aktif', 'selesai', 'dibatalkan', 'expired', 'reserved', 'applied', 'released', 'cancelled'])->default('aktif');
                $table->timestamps();

                $table->unique(['order_id', 'voucher_id'], 'unique_order_voucher');
            });
        } else {
            Schema::table('order_voucher_redemptions', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
                $table->dropForeign(['voucher_id']);
                $table->dropForeign(['customer_voucher_id']);
            });

            DB::statement("ALTER TABLE order_voucher_redemptions MODIFY COLUMN order_id BIGINT UNSIGNED NULL");
            DB::statement("ALTER TABLE order_voucher_redemptions MODIFY COLUMN customer_voucher_id BIGINT UNSIGNED NULL");
            DB::statement("ALTER TABLE order_voucher_redemptions MODIFY COLUMN status ENUM('aktif', 'selesai', 'dibatalkan', 'expired', 'reserved', 'applied', 'released', 'cancelled') NOT NULL DEFAULT 'aktif'");

            Schema::table('order_voucher_redemptions', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('restrict');
                $table->foreign('customer_voucher_id')->references('id')->on('customer_vouchers')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_voucher_redemptions')) {
            Schema::table('order_voucher_redemptions', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
                $table->dropForeign(['voucher_id']);
                $table->dropForeign(['customer_voucher_id']);
            });

            DB::statement("ALTER TABLE order_voucher_redemptions MODIFY COLUMN order_id BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE order_voucher_redemptions MODIFY COLUMN customer_voucher_id BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE order_voucher_redemptions MODIFY COLUMN status ENUM('reserved', 'applied', 'released', 'cancelled') NOT NULL DEFAULT 'reserved'");

            Schema::table('order_voucher_redemptions', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
                $table->foreign('customer_voucher_id')->references('id')->on('customer_vouchers')->onDelete('cascade');
            });
        }
    }
};
