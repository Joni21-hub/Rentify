<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cabangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_cabang');
            $table->text('alamat_lengkap');
            $table->string('koordinat')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('cabangs'); }
};
