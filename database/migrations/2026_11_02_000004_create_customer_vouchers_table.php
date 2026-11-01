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
        Schema::create('customer_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->enum('status', ['aktif', 'dipesan', 'terpakai', 'kedaluwarsa'])->default('aktif');
            $table->dateTime('claimed_at');
            $table->dateTime('used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'voucher_id'], 'unique_user_voucher_claim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_vouchers');
    }
};
