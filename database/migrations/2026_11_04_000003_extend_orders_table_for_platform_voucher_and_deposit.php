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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'voucher_platform_id')) {
                $table->foreignId('voucher_platform_id')
                    ->nullable()
                    ->after('voucher_id')
                    ->constrained('vouchers')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('orders', 'potongan_voucher_platform')) {
                $table->decimal('potongan_voucher_platform', 12, 2)
                    ->default(0.00)
                    ->after('voucher_platform_id');
            }

            if (!Schema::hasColumn('orders', 'total_deposit')) {
                $table->decimal('total_deposit', 12, 2)
                    ->default(0.00);
            } else {
                $table->decimal('total_deposit', 12, 2)
                    ->default(0.00)
                    ->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'total_deposit')) {
                $table->decimal('total_deposit', 15, 2)->default(0.00)->change();
            }
        });
    }
};
