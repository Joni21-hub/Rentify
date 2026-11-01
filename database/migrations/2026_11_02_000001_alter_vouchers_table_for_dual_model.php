<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Ubah vendor_id menjadi nullable untuk platform voucher
            $table->unsignedBigInteger('vendor_id')->nullable()->change();

            // Tambah kolom-kolom model dual voucher (toko & platform)
            $table->enum('tipe_voucher', ['toko', 'platform'])->default('toko')->after('kode_voucher');
            $table->enum('funding_source', ['vendor', 'platform'])->default('vendor')->after('tipe_voucher');
            $table->unsignedInteger('kuota_reserved')->default(0)->after('kuota_terpakai');
            $table->unsignedInteger('kuota_klaim_total')->nullable()->after('kuota_reserved');
            $table->unsignedInteger('kuota_klaim_terpakai')->default(0)->after('kuota_klaim_total');
            $table->decimal('anggaran_maksimal_platform', 14, 2)->nullable()->after('kuota_klaim_terpakai');
            $table->decimal('anggaran_reserved', 14, 2)->default(0.00)->after('anggaran_maksimal_platform');
            $table->decimal('anggaran_terpakai', 14, 2)->default(0.00)->after('anggaran_reserved');
            $table->enum('visibilitas', ['publik', 'rahasia'])->default('publik')->after('anggaran_terpakai');
            $table->enum('cakupan_produk', ['seluruh_toko', 'produk_spesifik', 'kategori_spesifik'])->default('seluruh_toko')->after('visibilitas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Platform vouchers tidak memiliki vendor_id sehingga harus dibersihkan sebelum vendor_id diubah menjadi NOT NULL
        DB::table('vouchers')->whereNull('vendor_id')->delete();

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'tipe_voucher',
                'funding_source',
                'kuota_reserved',
                'kuota_klaim_total',
                'kuota_klaim_terpakai',
                'anggaran_maksimal_platform',
                'anggaran_reserved',
                'anggaran_terpakai',
                'visibilitas',
                'cakupan_produk',
            ]);

            $table->unsignedBigInteger('vendor_id')->nullable(false)->change();
        });
    }
};
