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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->integer('min_quantity')->default(1)->after('kuota_klaim_terpakai');
            $table->boolean('suspended_by_admin')->default(false)->after('is_active');
            $table->boolean('auto_exhausted')->default(false)->after('suspended_by_admin');

            $table->index('suspended_by_admin', 'idx_vouchers_suspended_by_admin');
            $table->index(
                ['funding_source', 'is_active', 'suspended_by_admin', 'tanggal_mulai', 'tanggal_selesai'],
                'idx_vouchers_platform_eval'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex('idx_vouchers_platform_eval');
            $table->dropIndex('idx_vouchers_suspended_by_admin');
            $table->dropColumn([
                'min_quantity',
                'suspended_by_admin',
                'auto_exhausted',
            ]);
        });
    }
};
