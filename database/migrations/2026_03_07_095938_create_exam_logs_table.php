<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exam_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');

            // Log type
            $table->enum('type', [
                'info',
                'warning',
                'error',
                'security',
                'tab_switch',
                'connection_loss',
                'answer_saved',
                'question_navigation',
                'exam_start',
                'exam_submit',
                'exam_expire'
            ]);

            // Log details
            $table->text('message');
            $table->json('metadata')->nullable(); // Additional data

            // Device info
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['student_exam_id', 'type']);
            $table->index(['student_id', 'exam_id']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_logs');
    }
};
