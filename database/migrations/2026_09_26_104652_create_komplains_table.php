<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('komplains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('alasan');
            $table->string('bukti_foto')->nullable();
            $table->string('status_penyelesaian')->default('Menunggu Respon');
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('komplains'); }
};
