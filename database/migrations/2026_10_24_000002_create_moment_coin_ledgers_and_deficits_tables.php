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
        Schema::create('moment_coin_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('interactor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('moment_id')->nullable()->constrained('moments')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('interaction_type', [
                'like_mint',
                'unlike_deduct',
                'relike_restore',
                'checkout_spend',
                'refund',
                'post_deletion_clawback',
            ]);
            $table->integer('coins_amount');
            $table->unsignedInteger('remaining_lot_balance')->default(0);
            $table->boolean('minted_coin')->default(false);
            $table->timestamps();

            $table->index(['moment_id', 'interactor_id', 'interaction_type'], 'idx_user_moment_interactor');
            $table->index(['user_id', 'remaining_lot_balance', 'created_at'], 'idx_user_fifo_lot');
        });

        Schema::create('user_coin_deficits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('deficit_coins')->default(0);
            $table->timestamp('last_settled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_coin_deficits');
        Schema::dropIfExists('moment_coin_ledgers');
    }
};
