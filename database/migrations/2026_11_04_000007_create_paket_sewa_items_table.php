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
        Schema::dropIfExists('paket_sewa_items');

        Schema::create('paket_sewa_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_sewa_id')->constrained('paket_sewa')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang')->onDelete('restrict');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->unique(['paket_sewa_id', 'barang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_sewa_items');
    }
};
