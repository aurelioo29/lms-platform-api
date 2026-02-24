<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_question_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();

            $table->text('label');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(1);

            $table->timestamps();

            $table->index(['question_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_question_options');
    }
};
