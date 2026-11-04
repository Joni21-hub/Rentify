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
        Schema::create('order_item_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang')->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('harga_komponen_asli', 12, 2);
            $table->decimal('subtotal_sewa_asli', 12, 2);
            $table->decimal('subtotal_sewa_markup', 12, 2);
            $table->decimal('alokasi_diskon_toko', 12, 2)->default(0.00);
            $table->decimal('alokasi_diskon_platform', 12, 2)->default(0.00);
            $table->decimal('alokasi_diskon_koin', 12, 2)->default(0.00);
            $table->decimal('subtotal_sewa_bersih', 12, 2);
            $table->decimal('deposit_komponen', 12, 2)->default(0.00);
            $table->decimal('denda_komponen', 12, 2)->default(0.00);
            $table->enum('status_komponen', ['disewa', 'dikembalikan', 'rusak', 'hilang'])->default('disewa');
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_components');
    }
};
