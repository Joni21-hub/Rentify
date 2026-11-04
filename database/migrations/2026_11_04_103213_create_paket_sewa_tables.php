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

        if (!Schema::hasTable('paket_sewa_items')) {
            Schema::create('paket_sewa_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paket_sewa_id')->nullable()->constrained('paket_sewa')->onDelete('cascade');
                $table->foreignId('paket_id')->nullable()->constrained('barang')->onDelete('cascade');
                $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
                $table->unsignedInteger('quantity')->default(1);
                $table->timestamps();
            });
        } else {
            Schema::table('paket_sewa_items', function (Blueprint $table) {
                if (!Schema::hasColumn('paket_sewa_items', 'paket_id')) {
                    $table->foreignId('paket_id')->nullable()->after('id')->constrained('barang')->onDelete('cascade');
                }
                if (!Schema::hasColumn('paket_sewa_items', 'paket_sewa_id')) {
                    $table->foreignId('paket_sewa_id')->nullable()->after('id')->constrained('paket_sewa')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('paket_sewa_items') && Schema::hasColumn('paket_sewa_items', 'paket_id')) {
            Schema::table('paket_sewa_items', function (Blueprint $table) {
                $table->dropForeign(['paket_id']);
                $table->dropColumn('paket_id');
            });
        }

        if (Schema::hasColumn('barang', 'is_paket')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->dropColumn('is_paket');
            });
        }
    }
};

