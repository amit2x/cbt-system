<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('question_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->nullable()->constrained()->onDelete('cascade');

            // Performance metrics
            $table->integer('total_attempts')->default(0);
            $table->integer('correct_attempts')->default(0);
            $table->integer('incorrect_attempts')->default(0);
            $table->float('accuracy_rate')->default(0);

            // Time metrics
            $table->float('avg_time_seconds')->default(0);
            $table->float('min_time_seconds')->nullable();
            $table->float('max_time_seconds')->nullable();

            // Difficulty analysis
            $table->float('difficulty_index')->default(0); // % of students who got it wrong
            $table->float('discrimination_index')->default(0); // How well it distinguishes high/low performers

            // Option analysis (for MCQs)
            $table->json('option_analysis')->nullable(); // How many chose each option

            // Effectiveness
            $table->float('effectiveness_score')->nullable();
            $table->string('effectiveness_level')->nullable(); // 'good', 'average', 'poor'

            $table->timestamps();

            $table->index(['question_id', 'exam_id']);
            $table->index(['accuracy_rate', 'total_attempts']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('question_performance');
    }
};
