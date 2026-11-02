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
        Schema::create('financial_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_type', 20)->index(); // debit or credit
            $table->string('category', 100)->index(); // e.g. platform_commission, vendor_payout, etc.
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('IDR');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->unsignedBigInteger('campaign_id')->nullable()->index();
            $table->foreignId('pembayaran_id')->nullable()->constrained('pembayarans')->nullOnDelete();
            $table->string('reference_id', 100)->nullable()->index();
            $table->string('description', 255)->nullable();
            $table->decimal('running_balance', 12, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'entry_type']);
            $table->index(['created_at', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_ledger_entries');
    }
};
