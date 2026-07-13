<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            // Menambahkan kolom cover_photo setelah kolom stok_total
            $table->string('cover_photo')->nullable()->after('stok_total');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            // Menghapus kolom jika rollback
            $table->dropColumn('cover_photo');
        });
    }
};