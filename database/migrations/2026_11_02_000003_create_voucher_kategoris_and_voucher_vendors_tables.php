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
        Schema::create('voucher_kategoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('kategori_id')->constrained('kategoris')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['voucher_id', 'kategori_id'], 'unique_voucher_kategori');
        });

        Schema::create('voucher_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['voucher_id', 'vendor_id'], 'unique_voucher_vendor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_vendors');
        Schema::dropIfExists('voucher_kategoris');
    }
};
