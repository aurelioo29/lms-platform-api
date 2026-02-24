<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('reaction', ['like', 'love', 'laugh', 'insight', 'confused'])->default('like');

            // polymorphic target: course_discussions or discussion_comments
            $table->string('reactable_type');
            $table->unsignedBigInteger('reactable_id');

            $table->timestamp('created_at')->useCurrent();

            $table->index(['reactable_type', 'reactable_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
