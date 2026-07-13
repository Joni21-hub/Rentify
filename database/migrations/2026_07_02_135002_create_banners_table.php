<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('judul_promo');
            $table->string('gambar_url');
            $table->timestamps(); // Menghasilkan created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};