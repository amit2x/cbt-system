<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['mock_test', 'full_exam', 'quiz', 'practice'])->default('mock_test');
            $table->enum('category', ['daily', 'weekly', 'monthly', 'custom'])->default('custom');

            // Course/Batch Association
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');

            // Exam Settings
            $table->integer('duration_minutes')->default(60);
            $table->integer('total_marks')->default(0);
            $table->float('passing_percentage')->default(40);
            $table->float('negative_marking')->default(0);
            $table->boolean('randomize_questions')->default(false);
            $table->boolean('randomize_options')->default(false);
            $table->boolean('show_result_immediately')->default(true);
            $table->boolean('allow_review')->default(true);
            $table->integer('attempts_allowed')->default(1);

            // Scheduling
            $table->datetime('start_time')->nullable();
            $table->datetime('end_time')->nullable();
            $table->boolean('is_scheduled')->default(false);

            // Status
            $table->enum('status', ['draft', 'published', 'in_progress', 'completed', 'cancelled'])->default('draft');

            // Instructions
            $table->text('instructions')->nullable();

            // Metadata
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['type', 'status']);
            $table->index(['start_time', 'end_time']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exams');
    }
};
