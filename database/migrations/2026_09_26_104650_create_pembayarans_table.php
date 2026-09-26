<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('metode'); // qris, transfer
            $table->decimal('jumlah', 15, 2);
            $table->string('bukti_pembayaran')->nullable();
            $table->string('status')->default('Menunggu Konfirmasi'); 
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('pembayarans'); }
};
