<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // TAMBAHKAN BARIS INI:
            $table->unsignedBigInteger('vendor_id')->nullable(); 
            
            $table->string('customer_name');
            $table->string('customer_whatsapp');
            $table->text('shipping_address');
            $table->string('pin_location')->nullable();
            $table->string('shipping_method'); // 'ambil' atau 'antar'
            $table->integer('shipping_fee');
            $table->dateTime('start_rent');
            $table->dateTime('end_rent');
            $table->integer('duration_days');
            $table->string('payment_method'); // 'COD' atau 'QRIS'
            $table->integer('total_price');
            $table->string('status')->default('Menunggu Konfirmasi'); // Menunggu Konfirmasi, Berjalan, Selesai, Dibatalkan
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('orders'); }
};