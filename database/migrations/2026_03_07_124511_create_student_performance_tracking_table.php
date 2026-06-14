<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_performance_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('cascade');

            // Performance metrics
            $table->float('accuracy_trend')->nullable();
            $table->float('speed_trend')->nullable();
            $table->float('consistency_score')->nullable();
            $table->float('improvement_rate')->nullable();

            // Strengths and weaknesses
            $table->json('strengths')->nullable(); // Topics where performance is good
            $table->json('weaknesses')->nullable(); // Topics needing improvement

            // Time-based metrics
            $table->integer('total_time_spent')->default(0); // in minutes
            $table->integer('exams_attempted')->default(0);
            $table->integer('exams_passed')->default(0);
            $table->float('pass_rate')->default(0);

            // Predictive analytics
            $table->float('predicted_score')->nullable();
            $table->string('prediction_confidence')->nullable(); // 'high', 'medium', 'low'

            // Last updated
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['student_id', 'batch_id']);
            $table->index(['student_id', 'calculated_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_performance_tracking');
    }
};
