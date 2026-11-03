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
        Schema::table('moments', function (Blueprint $table) {
            $table->string('master_url', 500)->nullable()->after('media_urls');
            $table->string('master_hash', 64)->nullable()->after('master_url');
            $table->json('variants')->nullable()->after('master_hash');
            $table->unsignedInteger('width')->nullable()->after('variants');
            $table->unsignedInteger('height')->nullable()->after('width');
            $table->string('aspect_ratio', 20)->nullable()->after('height');
            $table->string('blurhash', 100)->nullable()->after('aspect_ratio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moments', function (Blueprint $table) {
            $table->dropColumn([
                'master_url',
                'master_hash',
                'variants',
                'width',
                'height',
                'aspect_ratio',
                'blurhash',
            ]);
        });
    }
};
