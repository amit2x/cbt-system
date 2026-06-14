<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_option_id')->nullable()->constrained()->onDelete('set null');

            // Answer data
            $table->text('answer_text')->nullable(); // For numerical or text answers
            $table->json('selected_options')->nullable(); // For MCQ answers (array of option IDs)
            $table->boolean('is_correct')->nullable();
            $table->float('marks_obtained')->nullable();
            $table->float('negative_marks_applied')->nullable();

            // Metadata
            $table->integer('time_taken_seconds')->nullable();
            $table->boolean('is_marked_for_review')->default(false);
            $table->boolean('is_visited')->default(false);
            $table->integer('visit_count')->default(0);

            // Auto-save tracking
            $table->datetime('last_saved_at');
            $table->integer('save_count')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['student_exam_id', 'question_id']);
            $table->index(['is_correct']);
            $table->index(['is_marked_for_review']);
            $table->unique(['student_exam_id', 'question_id'], 'unique_student_question');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_answers');
    }
};
