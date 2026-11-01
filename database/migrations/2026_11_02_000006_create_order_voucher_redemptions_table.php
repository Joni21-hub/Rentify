<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->string('payment_group_id', 64);
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('penyewaan_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('customer_voucher_id')->constrained('customer_vouchers')->onDelete('cascade');
            $table->enum('tipe_voucher', ['toko', 'platform']);
            $table->decimal('alokasi_diskon', 12, 2);
            $table->enum('status', ['reserved', 'applied', 'released', 'cancelled'])->default('reserved');
            $table->timestamps();

            $table->index('payment_group_id', 'idx_redemptions_payment_group');
            $table->unique(['order_id', 'voucher_id'], 'unique_order_voucher');
            $table->unique(['penyewaan_id', 'voucher_id'], 'unique_penyewaan_voucher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_voucher_redemptions');
    }
};
