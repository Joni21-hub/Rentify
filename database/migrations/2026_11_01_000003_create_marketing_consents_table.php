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
        Schema::create('marketing_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone_number', 50)->index();
            $table->string('consent_type', 50)->default('promotional')->index();
            $table->boolean('is_opted_in')->default(true)->index();
            $table->timestamp('opted_in_at')->nullable();
            $table->timestamp('opted_out_at')->nullable();
            $table->string('source', 100)->default('whatsapp_keyword');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['phone_number', 'consent_type'], 'uniq_phone_consent_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_consents');
    }
};
