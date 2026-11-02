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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->string('sender_type', 50)->default('customer')->index();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direction', 20)->default('inbound')->index();
            $table->string('message_type', 50)->default('text');
            $table->text('message')->nullable();
            $table->text('media_url')->nullable();
            $table->string('whatsapp_message_id', 191)->nullable()->index();
            $table->string('status', 50)->default('sent')->index();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
