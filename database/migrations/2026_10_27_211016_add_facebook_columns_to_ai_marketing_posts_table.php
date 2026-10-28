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
        Schema::table('ai_marketing_posts', function (Blueprint $table) {
            $table->boolean('posted_to_facebook')->default(false)->after('posted_to_tiktok');
            $table->string('facebook_post_id', 100)->nullable()->after('tiktok_post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_marketing_posts', function (Blueprint $table) {
            $table->dropColumn(['posted_to_facebook', 'facebook_post_id']);
        });
    }
};
