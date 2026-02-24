<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('event_type', 50);

            $table->string('ref_type', 50)->nullable(); // lesson, quiz, discussion, comment, enrollment, etc.
            $table->unsignedBigInteger('ref_id')->nullable();

            $table->json('meta_json')->nullable(); // ip, user_agent, score, etc.
            $table->timestamp('created_at')->useCurrent();

            $table->index('course_id');
            $table->index('user_id');
            $table->index('event_type');
            $table->index(['ref_type', 'ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
