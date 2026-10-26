<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membersihkan sisa-sisa kolom DOKU yang sudah tidak digunakan di tabel orders dan pembayarans.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = ['doku_invoice_number', 'doku_payment_url', 'doku_payment_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $cols = ['doku_payment_id', 'doku_channel'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('pembayarans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'doku_invoice_number')) {
                $table->string('doku_invoice_number')->nullable()->after('kode_booking');
            }
            if (!Schema::hasColumn('orders', 'doku_payment_url')) {
                $table->text('doku_payment_url')->nullable()->after('doku_invoice_number');
            }
            if (!Schema::hasColumn('orders', 'doku_payment_id')) {
                $table->string('doku_payment_id')->nullable()->after('doku_payment_url');
            }
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayarans', 'doku_payment_id')) {
                $table->string('doku_payment_id')->nullable()->after('metode');
            }
            if (!Schema::hasColumn('pembayarans', 'doku_channel')) {
                $table->string('doku_channel')->nullable()->after('doku_payment_id');
            }
        });
    }
};
