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
        if (!Schema::hasTable('moment_comments')) {
            Schema::create('moment_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('moment_id')->constrained('moments')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('comment');
                $table->timestamps();

                $table->index(['moment_id', 'created_at'], 'idx_moment_comments_moment_created');
            });
        }

        if (!Schema::hasColumn('moments', 'comments_count')) {
            Schema::table('moments', function (Blueprint $table) {
                $table->unsignedInteger('comments_count')->default(0)->after('likes_count');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moment_comments');

        if (Schema::hasColumn('moments', 'comments_count')) {
            Schema::table('moments', function (Blueprint $table) {
                $table->dropColumn('comments_count');
            });
        }
    }
};
