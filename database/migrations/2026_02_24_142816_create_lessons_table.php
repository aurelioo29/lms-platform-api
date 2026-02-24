<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('module_id')
                ->constrained('course_modules')
                ->cascadeOnDelete();

            $table->string('title');

            // Quill Delta JSON (preferred). If your MySQL doesn't support JSON well, change to longText.
            $table->json('content_json')->nullable();

            $table->enum('content_type', ['lesson', 'assignment', 'resource'])
                ->default('lesson');

            $table->unsignedInteger('sort_order')->default(1);

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete(); // teacher/admin

            $table->timestamp('published_at')->nullable();

            /**
             * LOCKING RULES (Option 2)
             * If unlock_after_lesson_id is null => unlocked
             * If not null => unlocked after the referenced lesson is opened/completed (depending on lock_mode)
             */
            $table->foreignId('unlock_after_lesson_id')
                ->nullable()
                ->constrained('lessons')
                ->nullOnDelete();

            $table->enum('lock_mode', ['open', 'complete'])
                ->default('open');

            $table->timestamps();

            $table->index(['module_id', 'sort_order']);
            $table->index('unlock_after_lesson_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
