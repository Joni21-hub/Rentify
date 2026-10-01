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
            if (!Schema::hasColumn('orders', 'total_price')) {
                $table->decimal('total_price', 15, 2)->nullable()->after('total_biaya');
            }
            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('alamat_pengiriman');
            }
            if (!Schema::hasColumn('orders', 'shipping_method')) {
                $table->string('shipping_method')->nullable()->after('metode_pengambilan');
            }
            if (!Schema::hasColumn('orders', 'start_rent')) {
                $table->dateTime('start_rent')->nullable()->after('tanggal_mulai');
            }
            if (!Schema::hasColumn('orders', 'end_rent')) {
                $table->dateTime('end_rent')->nullable()->after('tanggal_selesai');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['total_price', 'shipping_address', 'shipping_method', 'start_rent', 'end_rent'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
