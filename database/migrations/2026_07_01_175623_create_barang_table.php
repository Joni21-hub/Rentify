<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            // Asumsi vendor_id nyambung ke tabel users (karena vendor adalah user)
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade'); 
            
            // Kolom lainnya sesuai Model kamu
            $table->unsignedBigInteger('kategori_id')->nullable(); 
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->json('spesifikasi')->nullable(); 
            $table->decimal('harga_sewa_harian', 15, 2);
            $table->decimal('deposit', 15, 2)->default(0);
            $table->decimal('denda_per_hari', 15, 2)->default(0);
            $table->string('kondisi')->nullable();
            $table->integer('stok_total')->default(0);
            $table->string('status')->default('tersedia');
            $table->boolean('is_approved')->default(false);
            $table->dateTime('approved_at')->nullable();
            $table->string('cover_image')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};