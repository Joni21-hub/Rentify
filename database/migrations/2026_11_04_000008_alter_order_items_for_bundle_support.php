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
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('product_id')->nullable()->change();

            if (!Schema::hasColumn('order_items', 'paket_sewa_id')) {
                $table->foreignId('paket_sewa_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('paket_sewa')
                    ->nullOnDelete();
            } else {
                $fkExists = DB::table('information_schema.TABLE_CONSTRAINTS')
                    ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                    ->where('TABLE_NAME', 'order_items')
                    ->where('CONSTRAINT_NAME', 'order_items_paket_sewa_id_foreign')
                    ->exists();

                if (!$fkExists) {
                    $table->foreign('paket_sewa_id')
                        ->references('id')
                        ->on('paket_sewa')
                        ->nullOnDelete();
                }
            }

            if (!Schema::hasColumn('order_items', 'item_type')) {
                $table->enum('item_type', ['barang', 'paket'])
                    ->default('barang')
                    ->after('paket_sewa_id');
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
              AND TABLE_NAME = 'order_items' 
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        "))->pluck('CONSTRAINT_NAME')->toArray();

        Schema::table('order_items', function (Blueprint $table) use ($foreignKeys) {
            if (in_array('order_items_paket_sewa_id_foreign', $foreignKeys)) {
                $table->dropForeign(['paket_sewa_id']);
            }
            if (Schema::hasColumn('order_items', 'paket_sewa_id')) {
                $table->dropColumn('paket_sewa_id');
            }
            if (Schema::hasColumn('order_items', 'item_type')) {
                $table->dropColumn('item_type');
            }
        });

        // Ensure no null product_id values remain before restoring NOT NULL constraint
        DB::statement("UPDATE order_items SET product_id = '' WHERE product_id IS NULL");

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('product_id')->nullable(false)->change();
        });
    }
};
