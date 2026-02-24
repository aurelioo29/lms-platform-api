<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // student

            $table->unsignedInteger('attempt_no')->default(1);
            $table->enum('status', ['in_progress', 'submitted', 'graded'])->default('in_progress');

            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();

            $table->unsignedInteger('score')->nullable();

            $table->timestamps();

            $table->index(['quiz_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
