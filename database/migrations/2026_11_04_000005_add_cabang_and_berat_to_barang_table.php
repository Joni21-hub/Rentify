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
        Schema::table('barang', function (Blueprint $table) {
            if (!Schema::hasColumn('barang', 'cabang_id')) {
                $table->foreignId('cabang_id')
                    ->nullable()
                    ->after('vendor_id')
                    ->constrained('cabangs')
                    ->nullOnDelete();
            } else {
                $fkExists = DB::table('information_schema.TABLE_CONSTRAINTS')
                    ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                    ->where('TABLE_NAME', 'barang')
                    ->where('CONSTRAINT_NAME', 'barang_cabang_id_foreign')
                    ->exists();

                if (!$fkExists) {
                    $table->foreign('cabang_id')
                        ->references('id')
                        ->on('cabangs')
                        ->nullOnDelete();
                }
            }

            if (!Schema::hasColumn('barang', 'berat_gram')) {
                $table->integer('berat_gram')
                    ->default(1000)
                    ->after('stok_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $foreignKeys = collect(DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'barang' 
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        "))->pluck('CONSTRAINT_NAME')->toArray();

        Schema::table('barang', function (Blueprint $table) use ($foreignKeys) {
            if (in_array('barang_cabang_id_foreign', $foreignKeys)) {
                $table->dropForeign(['cabang_id']);
            }
            if (Schema::hasColumn('barang', 'cabang_id')) {
                $table->dropColumn('cabang_id');
            }
            if (Schema::hasColumn('barang', 'berat_gram')) {
                $table->dropColumn('berat_gram');
            }
        });
    }
};
