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
            if (!Schema::hasColumn('orders', 'koin_digunakan')) {
                $table->unsignedInteger('koin_digunakan')->default(0)->after('potongan_voucher');
            }
            if (!Schema::hasColumn('orders', 'potongan_koin')) {
                $table->decimal('potongan_koin', 15, 2)->default(0)->after('koin_digunakan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'potongan_koin')) {
                $table->dropColumn('potongan_koin');
            }
            if (Schema::hasColumn('orders', 'koin_digunakan')) {
                $table->dropColumn('koin_digunakan');
            }
        });
    }
};
