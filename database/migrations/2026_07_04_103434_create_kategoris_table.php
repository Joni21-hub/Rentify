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
        Schema::create('kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Nama kategori (misal: Kamera, Tenda, dll)
            $table->boolean('is_active')->default(1); // Status aktif (1) atau tidak (0)
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategoris');
    }
};
