<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('barang', function (Blueprint $table) {
            $table->text('alamat')->nullable()->after('is_delivery_supported');
            $table->string('latitude')->nullable()->after('alamat');
            $table->string('longitude')->nullable()->after('latitude');
        });
    }
    public function down(): void {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'latitude', 'longitude']);
        });
    }
};