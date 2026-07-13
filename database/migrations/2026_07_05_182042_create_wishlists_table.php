<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Pengecekan: Jika tabel belum ada, baru dibuat. Jika sudah ada, lewati.
        if (!Schema::hasTable('wishlists')) {
            Schema::create('wishlists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                // Mengarahkan ke tabel 'barang', bukan default 'barangs'
                $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }
    
    public function down() 
    { 
        Schema::dropIfExists('wishlists'); 
    }
};