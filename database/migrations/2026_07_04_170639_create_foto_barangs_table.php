<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::create('foto_barangs', function (Blueprint $table) {
            $table->id();
            // Menyambungkan ke tabel barang
            $table->foreignId('barang_id')->references('id')->on('barang')->onDelete('cascade'); 
            $table->string('foto_path'); // Alamat simpan foto
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foto_barangs');
    }
};
