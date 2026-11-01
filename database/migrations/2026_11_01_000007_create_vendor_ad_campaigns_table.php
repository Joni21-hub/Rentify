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
        Schema::create('vendor_ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('barang_id')->nullable()->constrained('barang')->cascadeOnDelete();
            $table->string('campaign_name', 255);
            $table->string('billing_model', 50)->index(); // cpc, ppb, flat_daily, flat_weekly
            $table->decimal('bid_amount', 12, 2)->default(0.00);
            $table->decimal('ppb_commission_rate', 5, 2)->nullable();
            $table->decimal('daily_budget', 12, 2)->nullable();
            $table->decimal('total_budget', 12, 2)->nullable();
            $table->decimal('current_daily_spend', 12, 2)->default(0.00);
            $table->decimal('total_spend', 12, 2)->default(0.00);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('status', 50)->default('active')->index(); // active, paused, depleted_suspended, expired, completed
            $table->json('target_keywords')->nullable();
            $table->json('target_categories')->nullable();
            $table->unsignedInteger('impressions_count')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->unsignedInteger('bookings_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
            $table->index(['status', 'billing_model']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_ad_campaigns');
    }
};
