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
        Schema::table('ulasans', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable()->change();

            if (!Schema::hasColumn('ulasans', 'paket_sewa_id')) {
                $table->foreignId('paket_sewa_id')
                    ->nullable()
                    ->after('barang_id')
                    ->constrained('paket_sewa')
                    ->nullOnDelete();
            } else {
                $fkExists = DB::table('information_schema.TABLE_CONSTRAINTS')
                    ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                    ->where('TABLE_NAME', 'ulasans')
                    ->where('CONSTRAINT_NAME', 'ulasans_paket_sewa_id_foreign')
                    ->exists();

                if (!$fkExists) {
                    $table->foreign('paket_sewa_id')
                        ->references('id')
                        ->on('paket_sewa')
                        ->nullOnDelete();
                }
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
              AND TABLE_NAME = 'ulasans' 
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        "))->pluck('CONSTRAINT_NAME')->toArray();

        Schema::table('ulasans', function (Blueprint $table) use ($foreignKeys) {
            if (in_array('ulasans_paket_sewa_id_foreign', $foreignKeys)) {
                $table->dropForeign(['paket_sewa_id']);
            }
            if (Schema::hasColumn('ulasans', 'paket_sewa_id')) {
                $table->dropColumn('paket_sewa_id');
            }
        });

        // Ensure no null barang_id records exist before restoring NOT NULL constraint
        DB::table('ulasans')->whereNull('barang_id')->delete();

        Schema::table('ulasans', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable(false)->change();
        });
    }
};
