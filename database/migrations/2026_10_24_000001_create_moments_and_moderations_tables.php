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
        Schema::create('moments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('media_url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->enum('media_type', ['image', 'video'])->default('image');
            $table->decimal('video_duration_seconds', 4, 2)->nullable();
            $table->text('caption')->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('lifetime_minted_count')->default(0);
            $table->unsignedInteger('bookmarks_count')->default(0);
            $table->unsignedInteger('reports_count')->default(0);
            $table->decimal('ranking_score', 10, 4)->default(0);
            $table->enum('status', ['active', 'under_moderation', 'deleted'])->default('active');
            $table->timestamps();

            $table->index(['status', 'ranking_score', 'id'], 'idx_moments_feed');
            $table->index(['status', 'created_at'], 'idx_moments_recency');
        });

        Schema::create('moment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moment_id')->constrained('moments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['moment_id', 'user_id'], 'unq_moment_user_like');
        });

        Schema::create('moment_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('moment_id')->constrained('moments')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'moment_id'], 'unq_user_moment_bookmark');
        });

        Schema::create('moment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moment_id')->constrained('moments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('category', ['spam', 'inappropriate', 'fraud', 'copyright']);
            $table->text('reason_description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['moment_id', 'user_id'], 'unq_user_moment_report');
        });

        Schema::create('moment_moderation_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moment_id')->constrained('moments')->onDelete('cascade');
            $table->enum('status', ['pending', 'reviewed', 'dismissed'])->default('pending');
            $table->unsignedInteger('total_reports')->default(0);
            $table->timestamp('last_reported_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'idx_moderation_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moment_moderation_queue');
        Schema::dropIfExists('moment_reports');
        Schema::dropIfExists('moment_bookmarks');
        Schema::dropIfExists('moment_likes');
        Schema::dropIfExists('moments');
    }
};
