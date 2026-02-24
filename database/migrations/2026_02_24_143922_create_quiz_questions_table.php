<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();

            $table->enum('question_type', [
                'mcq_single',
                'mcq_multi',
                'essay',
                'rating',
                'matching',
                'true_false'
            ]);

            $table->text('prompt');
            $table->json('prompt_json')->nullable();
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(1);

            $table->timestamps();

            $table->index(['quiz_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
