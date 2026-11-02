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
        Schema::create('referral_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referee_id')->constrained('users')->cascadeOnDelete();
            $table->string('referral_code', 50)->index();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('status', 50)->default('pending_escrow')->index();
            $table->decimal('gross_booking_amount', 12, 2)->default(0.00);
            $table->decimal('reward_amount', 12, 2)->default(0.00);
            $table->foreignId('referee_voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            $table->decimal('anti_fraud_score', 5, 2)->nullable();
            $table->string('fraud_check_status', 50)->default('passed')->index();
            $table->json('fraud_reasons')->nullable();
            $table->timestamp('escrow_matures_at')->nullable();
            $table->timestamp('clawback_at')->nullable();
            $table->boolean('is_payout_locked')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_records');
    }
};
