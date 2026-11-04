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
        if (!Schema::hasColumn('barang', 'is_paket')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->boolean('is_paket')->default(false)->after('kondisi')->index();
            });
        }

        if (Schema::hasTable('paket_sewa_items') && !Schema::hasColumn('paket_sewa_items', 'paket_id')) {
            Schema::dropIfExists('paket_sewa_items');
        }

        if (!Schema::hasTable('paket_sewa_items')) {
            Schema::create('paket_sewa_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paket_id')->constrained('barang')->onDelete('cascade');
                $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
                $table->unsignedInteger('quantity')->default(1);
                $table->timestamps();

                $table->unique(['paket_id', 'barang_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_sewa_items');

        if (Schema::hasColumn('barang', 'is_paket')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->dropColumn('is_paket');
            });
        }
    }
};

