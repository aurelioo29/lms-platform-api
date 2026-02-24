<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_instructors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // teacher

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete(); // admin
            $table->timestamp('assigned_at');

            $table->timestamps();

            $table->unique(['course_id', 'user_id']);
            $table->index(['course_id', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_instructors');
    }
};
