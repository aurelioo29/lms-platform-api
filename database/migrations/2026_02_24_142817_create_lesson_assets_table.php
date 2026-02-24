<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lesson_assets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->enum('type', ['pdf', 'video_embed', 'video_upload', 'image', 'file']);
            $table->string('title')->nullable();

            $table->text('url')->nullable();
            $table->text('file_path')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('lesson_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_assets');
    }
};
