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
        Schema::create('vendor_ad_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('vendor_ad_campaigns')->cascadeOnDelete();
            $table->foreignId('barang_id')->nullable()->constrained('barang')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('chat_session_id')->nullable()->constrained('chat_sessions')->nullOnDelete();
            $table->string('session_token', 100)->nullable()->index();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('interaction_type', 50)->default('chatbot_button'); // chatbot_button, catalog_link, external_url, impression
            $table->boolean('is_billable')->default(true)->index();
            $table->string('deduplication_reason', 100)->nullable(); // cooling_off_24h, bot_detected, passive_impression
            $table->decimal('charged_amount', 12, 2)->default(0.00);
            $table->foreignId('attributed_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('attributed_at')->nullable();
            $table->timestamp('clicked_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'ip_hash', 'clicked_at'], 'idx_campaign_ip_clicked');
            $table->index(['campaign_id', 'is_billable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_ad_clicks');
    }
};
