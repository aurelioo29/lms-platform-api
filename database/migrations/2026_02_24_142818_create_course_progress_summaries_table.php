<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_progress_summaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedTinyInteger('completion_percent')->default(0);
            $table->unsignedInteger('completed_lessons_count')->default(0);
            $table->unsignedInteger('total_lessons_count')->default(0);

            $table->timestamp('updated_at')->nullable();

            $table->unique(['course_id', 'user_id']);
            $table->index(['course_id', 'completion_percent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_progress_summaries');
    }
};
