<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('proctoring_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('monitoring_session_id')->nullable()->constrained('live_monitoring_sessions')->onDelete('set null');

            // Image data
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->integer('image_size')->nullable(); // in bytes

            // Metadata
            $table->json('metadata')->nullable(); // resolution, timestamp, etc.
            $table->enum('capture_type', ['automatic', 'manual'])->default('automatic');
            $table->boolean('is_suspicious')->default(false);
            $table->text('suspicious_reason')->nullable();
            $table->boolean('reviewed')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['student_exam_id', 'created_at']);
            $table->index(['is_suspicious', 'reviewed']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('proctoring_images');
    }
};
