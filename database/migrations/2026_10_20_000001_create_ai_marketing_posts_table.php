<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_marketing_posts', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->unsignedTinyInteger('day_number');
            $table->string('day_type', 10); // 'ganjil' | 'genap'
            $table->string('target_audience', 20); // 'customer' | 'vendor'
            $table->string('tema', 191);
            $table->string('kategori_barang', 100)->nullable();
            $table->string('persona_vendor', 100)->nullable();
            $table->text('hook_masalah');
            $table->json('naskah_video'); // Breakdown adegan 15-30 detik (hook, dilema, solusi Rentify, cta)
            $table->json('teks_di_layar'); // Array overlay teks vertikal 9:16
            $table->text('caption'); // Caption persuasif + hashtag + link web
            $table->text('visual_prompt')->nullable(); // Deskripsi visual komposisi 9:16
            $table->string('visual_media_url', 255)->nullable(); // URL export gambar/visual 9:16
            $table->string('ai_source', 100)->default('Gemini AI');
            $table->string('status', 30)->default('ready'); // ready, published, failed
            $table->boolean('posted_to_instagram')->default(false);
            $table->boolean('posted_to_tiktok')->default(false);
            $table->string('instagram_post_id', 100)->nullable();
            $table->string('tiktok_post_id', 100)->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->text('log_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_marketing_posts');
    }
};
