<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            // video attached to question (optional)
            $table->enum('media_type', ['none', 'upload', 'youtube'])
                ->default('none')
                ->after('question_type');

            // if youtube: store URL (or you can store youtube_id)
            $table->string('media_url')->nullable()->after('media_type');

            // if upload: store storage path like "quiz/questions/{id}/video.mp4"
            $table->string('media_path')->nullable()->after('media_url');

            // optional meta: duration_seconds, thumbnail_url, provider, etc
            $table->json('media_meta')->nullable()->after('media_path');

            // if true, UI/server can require watching before answering
            $table->boolean('require_watch')->default(false)->after('media_meta');

            // optional: allow partial requirement (e.g., must watch 30s)
            $table->unsignedInteger('min_watch_seconds')->nullable()->after('require_watch');

            // sanity: quick search/filter
            $table->index(['quiz_id', 'media_type']);
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropIndex(['quiz_questions_quiz_id_media_type_index']);

            $table->dropColumn([
                'media_type',
                'media_url',
                'media_path',
                'media_meta',
                'require_watch',
                'min_watch_seconds',
            ]);
        });
    }
};
