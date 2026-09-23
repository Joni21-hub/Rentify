<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Menyimpan ID voucher yang dipakai (bisa kosong jika tidak pakai)
            $table->foreignId('voucher_id')->nullable()->after('payment_method')->constrained('vouchers')->onDelete('set null');
            // Menyimpan nominal diskon yang didapat
            $table->decimal('potongan_voucher', 15, 2)->default(0)->after('total_price');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
            $table->dropColumn(['voucher_id', 'potongan_voucher']);
        });
    }
};