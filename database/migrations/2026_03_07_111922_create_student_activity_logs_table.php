<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('monitoring_session_id')->nullable()->constrained('live_monitoring_sessions')->onDelete('set null');

            // Activity type
            $table->enum('activity_type', [
                'tab_switch',
                'window_blur',
                'window_focus',
                'mouse_leave',
                'mouse_enter',
                'copy_attempt',
                'paste_attempt',
                'right_click',
                'devtools_open',
                'screenshot_taken',
                'connection_lost',
                'connection_restored',
                'warning_issued',
                'exam_terminated'
            ]);

            // Activity details
            $table->json('activity_data')->nullable();
            $table->text('description')->nullable();

            // Severity
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');

            // Timestamp
            $table->timestamp('occurred_at');

            $table->timestamps();

            $table->index(['student_exam_id', 'activity_type']);
            $table->index(['student_id', 'occurred_at']);
            $table->index(['severity', 'occurred_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_activity_logs');
    }
};
