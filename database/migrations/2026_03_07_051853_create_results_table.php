<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');

            // Score details
            $table->float('total_marks');
            $table->float('obtained_marks');
            $table->float('percentage');
            $table->enum('status', ['passed', 'failed', 'pending'])->default('pending');

            // Question counts
            $table->integer('total_questions');
            $table->integer('correct_answers');
            $table->integer('incorrect_answers');
            $table->integer('unanswered');
            $table->integer('marked_for_review')->default(0);

            // Time metrics
            $table->integer('time_taken_seconds');
            $table->float('speed_per_minute')->nullable(); // Questions per minute

            // Ranking
            $table->integer('rank')->nullable();
            $table->integer('percentile')->nullable();
            $table->integer('total_participants')->nullable();

            // Detailed analysis (JSON for flexibility)
            $table->json('sectional_analysis')->nullable();
            $table->json('topic_analysis')->nullable();
            $table->json('difficulty_analysis')->nullable();
            $table->json('accuracy_by_type')->nullable();

            // Performance metrics
            $table->float('accuracy_percentage')->nullable();
            $table->float('speed_score')->nullable(); // Based on time taken vs average
            $table->float('consistency_score')->nullable(); // Based on section-wise performance

            // Certificate (if applicable)
            $table->string('certificate_url')->nullable();
            $table->timestamp('certificate_generated_at')->nullable();

            $table->timestamps();

            // Indexes for fast queries
            $table->index(['student_id', 'exam_id']);
            $table->index(['exam_id', 'percentage']);
            $table->index(['exam_id', 'rank']);
            $table->index(['batch_id', 'status']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('results');
    }
};
