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
        Schema::create('moment_tagged_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moment_id')->constrained('moments')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('barang')->onDelete('cascade');
            $table->unsignedTinyInteger('display_order')->default(1);
            $table->timestamps();

            $table->unique(['moment_id', 'product_id'], 'unq_moment_product_tag');
        });

        Schema::create('moment_ai_tag_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moment_id')->constrained('moments')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('barang')->onDelete('cascade');
            $table->decimal('confidence_score', 5, 4);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at')->nullable()->index();
        });

        Schema::create('product_stock_waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('barang')->onDelete('cascade');
            $table->enum('status', ['pending', 'notified'])->default('pending')->index();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('notified_at')->nullable();

            $table->unique(['user_id', 'product_id'], 'unq_user_product_waitlist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_waitlists');
        Schema::dropIfExists('moment_ai_tag_suggestions');
        Schema::dropIfExists('moment_tagged_products');
    }
};
