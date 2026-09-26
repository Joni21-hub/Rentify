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
            $table->unsignedBigInteger('vendor_id')->nullable(); 
            
            // Kolom disesuaikan dengan Model Penyewaan
            $table->string('kode_booking')->unique();
            $table->unsignedBigInteger('cabang_id')->nullable();
            
            $table->string('customer_name');
            $table->string('customer_whatsapp');
            $table->text('alamat_pengiriman')->nullable();
            $table->string('pin_location')->nullable();
            
            $table->string('metode_pengambilan'); // 'ambil' atau 'antar'
            $table->integer('shipping_fee')->default(0);
            
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->dateTime('tanggal_kembali_aktual')->nullable();
            $table->integer('duration_days')->default(1);
            
            $table->string('payment_method'); // 'COD' atau 'QRIS' atau 'Transfer'
            $table->decimal('total_biaya', 15, 2);
            $table->decimal('total_deposit', 15, 2)->default(0);
            $table->decimal('total_denda', 15, 2)->default(0);
            
            $table->string('status')->default('Menunggu Konfirmasi'); // Menunggu Konfirmasi, Berjalan, Selesai, Dibatalkan
            
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('orders'); }
};