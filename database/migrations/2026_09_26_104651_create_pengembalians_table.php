<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pengembalians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->dateTime('tanggal_dikembalikan');
            $table->string('kondisi_barang')->nullable();
            $table->decimal('denda_keterlambatan', 15, 2)->default(0);
            $table->decimal('denda_kerusakan', 15, 2)->default(0);
            $table->string('status')->default('Diajukan'); 
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('pengembalians'); }
};
