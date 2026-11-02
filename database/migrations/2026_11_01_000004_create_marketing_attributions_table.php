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
        Schema::create('marketing_attributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->nullable()->constrained('chat_sessions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('touchpoint_type', 50)->default('touchpoint')->index();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('utm_term', 100)->nullable();
            $table->string('utm_content', 100)->nullable();
            $table->string('gclid', 255)->nullable()->index();
            $table->string('fbclid', 255)->nullable()->index();
            $table->string('fbp', 100)->nullable()->index();
            $table->string('fbc', 100)->nullable()->index();
            $table->string('gbraid', 255)->nullable()->index();
            $table->string('wbraid', 255)->nullable()->index();
            $table->string('referral_code', 50)->nullable()->index();
            $table->text('landing_page_url')->nullable();
            $table->text('referrer_url')->nullable();
            $table->string('client_ip_address', 45)->nullable();
            $table->text('client_user_agent')->nullable();
            $table->decimal('conversion_value', 12, 2)->nullable();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_attributions');
    }
};
