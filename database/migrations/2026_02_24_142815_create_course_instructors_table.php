<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_instructors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // optional: main vs assistant, etc
            $table->enum('role', ['main', 'assistant'])->default('main');

            // optional: siapa yang assign
            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['course_id', 'user_id']);
            $table->index(['course_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_instructors');
    }
};
