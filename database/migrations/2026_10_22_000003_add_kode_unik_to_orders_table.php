<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'kode_unik')) {
                $table->integer('kode_unik')->default(0)->after('shipping_fee')->comment('3-digit kode unik verifikasi mutasi QRIS otomatis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'kode_unik')) {
                $table->dropColumn('kode_unik');
            }
        });
    }
};
