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
        Schema::create('social_proof_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ulasan_id')->nullable()->constrained('ulasans')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(5)->index();
            $table->text('review_text')->nullable();
            $table->json('media_urls')->nullable();
            $table->json('sanitized_media_urls')->nullable();
            $table->boolean('is_consent_granted')->default(false)->index();
            $table->timestamp('consent_granted_at')->nullable();
            $table->timestamp('consent_revoked_at')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->string('status', 50)->default('pending_review')->index(); // pending_review, published, held_pending_mediation, revoked, rejected
            $table->decimal('incentive_reward_amount', 12, 2)->default(0.00);
            $table->foreignId('incentive_voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            $table->string('service_recovery_ticket_id', 100)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['rating', 'is_published']);
            $table->index(['barang_id', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_proof_reviews');
    }
};
