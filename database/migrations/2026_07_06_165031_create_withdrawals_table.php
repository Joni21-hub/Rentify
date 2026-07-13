<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->decimal('nominal', 15, 2);
            $table->enum('metode', ['Bank', 'E-Wallet']);
            $table->string('nama_bank_ewallet');
            $table->string('nomor_rekening');
            $table->string('nama_pemilik');
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('withdrawals'); }
};