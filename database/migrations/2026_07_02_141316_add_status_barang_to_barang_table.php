<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            // Membuang ->after('harga_sewa') supaya ia dimasukkan ke baris akhir secara selamat
            $table->string('status_barang')->default('pending');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('status_barang');
        });
    }
};