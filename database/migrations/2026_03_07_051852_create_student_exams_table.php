<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');

            // Exam attempt tracking
            $table->datetime('started_at');
            $table->datetime('submitted_at')->nullable();
            $table->integer('remaining_seconds');
            $table->integer('total_seconds_taken')->nullable();

            // Questions state
            $table->json('question_order')->nullable(); // Randomized question order
            $table->json('answers_state')->nullable(); // Quick access to answered questions
            $table->json('marked_for_review')->nullable(); // Questions marked for review

            // Status tracking
            $table->enum('status', [
                'pending',
                'started',
                'in_progress',
                'paused',
                'completed',
                'expired',
                'terminated'
            ])->default('pending');

            // Security & monitoring
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('tab_switch_log')->nullable(); // Track tab switches
            $table->integer('tab_switch_count')->default(0);
            $table->boolean('is_proctored')->default(false);

            // Result summary (cached for quick access)
            $table->integer('total_questions')->default(0);
            $table->integer('answered_questions')->default(0);
            $table->integer('marked_for_review_count')->default(0);
            $table->integer('not_visited_count')->default(0);

            // Score (updated when exam completes)
            $table->float('obtained_marks')->nullable();
            $table->float('percentage')->nullable();
            $table->integer('correct_answers')->nullable();
            $table->integer('incorrect_answers')->nullable();
            $table->integer('unanswered')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['student_id', 'status']);
            $table->index(['exam_id', 'status']);
            $table->index(['started_at']);
            $table->unique(['student_id', 'exam_id', 'batch_id'], 'unique_student_exam_attempt');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_exams');
    }
};
