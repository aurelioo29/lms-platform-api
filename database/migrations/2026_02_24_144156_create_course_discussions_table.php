<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_discussions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // student

            $table->string('title');
            $table->json('body_json'); // Quill delta
            $table->enum('status', ['open', 'locked', 'hidden'])->default('open');

            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index(['course_id', 'user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_discussions');
    }
};
