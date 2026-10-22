<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('app_name', 50)->default('DANA'); // DANA, BCA, GOPAY, SHOPEEPAY, BRI, etc.
            $table->string('package_name', 100)->nullable(); // id.dana, com.bca, etc.
            $table->string('tipe', 10)->default('CR'); // CR (Kredit/Masuk) atau DB (Debit/Keluar)
            $table->decimal('nominal', 15, 2)->index();
            $table->string('sender', 150)->nullable(); // Pengirim / Catatan jika terdeteksi
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('status', 30)->default('UNMATCHED'); // MATCHED, UNMATCHED, DUPLICATE, IGNORED
            $table->text('catatan')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasis');
    }
};
