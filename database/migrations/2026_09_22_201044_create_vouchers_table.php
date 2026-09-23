<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->string('kode_voucher')->unique(); // Contoh: "D1SKON50"
            $table->enum('tipe_diskon', ['nominal', 'persen']); 
            $table->decimal('nilai_diskon', 15, 2); // Nominal Rp atau Angka %
            $table->decimal('maksimal_diskon', 15, 2)->nullable(); // Khusus untuk tipe persen
            $table->decimal('minimal_belanja', 15, 2)->default(0); 
            $table->integer('kuota_total')->default(0); // Berapa kali bisa dipakai
            $table->integer('kuota_terpakai')->default(0);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};