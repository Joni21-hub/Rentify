<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('saldos', function (Blueprint $table) {
            $table->id();
            // Asumsi tabel vendor gabung dengan users. Sesuaikan jika tabelnya bernama 'vendors'
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade'); 
            $table->decimal('saldo_aktif', 15, 2)->default(0); // Saldo yang bisa ditarik
            $table->decimal('saldo_ditahan', 15, 2)->default(0); // Saldo yang sedang ditarik (pending)
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('saldos'); }
};