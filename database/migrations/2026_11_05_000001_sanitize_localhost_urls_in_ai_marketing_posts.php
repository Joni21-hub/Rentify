<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ai_marketing_posts')) {
            DB::table('ai_marketing_posts')
                ->where('caption', 'LIKE', '%localhost%')
                ->orWhere('teks_di_layar', 'LIKE', '%localhost%')
                ->get()
                ->each(function ($post) {
                    $caption = str_replace(
                        ['http://localhost/vendor/register', 'http://localhost', 'localhost'],
                        ['https://rentify21.my.id/vendor/register', 'https://rentify21.my.id', 'rentify21.my.id'],
                        $post->caption ?? ''
                    );
                    $teksDiLayar = str_replace(
                        ['http://localhost/vendor/register', 'http://localhost', 'localhost'],
                        ['https://rentify21.my.id/vendor/register', 'https://rentify21.my.id', 'rentify21.my.id'],
                        $post->teks_di_layar ?? ''
                    );

                    DB::table('ai_marketing_posts')->where('id', $post->id)->update([
                        'caption' => $caption,
                        'teks_di_layar' => $teksDiLayar,
                    ]);
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: Canonical domain is the desired state
    }
};
