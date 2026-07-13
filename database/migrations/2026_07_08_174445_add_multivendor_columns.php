<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Menambahkan Titik Koordinat Lokasi di tabel Users (Toko/Vendor & Customer)
        Schema::table('users', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('whatsapp_vendor');
            $table->string('longitude')->nullable()->after('latitude');
        });

        // 2. Menambahkan status ketersediaan pengantaran di tabel Barang
        Schema::table('barang', function (Blueprint $table) {
            $table->boolean('is_delivery_supported')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('is_delivery_supported');
        });
    }
};