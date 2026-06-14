<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('live_monitoring_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('cascade');

            // Session details
            $table->string('session_name');
            $table->datetime('started_at');
            $table->datetime('ended_at')->nullable();
            $table->enum('status', ['active', 'paused', 'ended'])->default('active');

            // Monitoring settings
            $table->boolean('track_tab_switches')->default(true);
            $table->boolean('track_mouse_events')->default(false);
            $table->boolean('enable_proctoring')->default(false);
            $table->integer('screenshot_interval')->nullable(); // seconds between screenshots

            // Alerts configuration
            $table->json('alert_settings')->nullable();

            // Statistics (cached)
            $table->integer('total_students')->default(0);
            $table->integer('active_students')->default(0);
            $table->integer('warning_count')->default(0);
            $table->integer('terminated_count')->default(0);

            $table->timestamps();

            $table->index(['exam_id', 'status']);
            $table->index(['instructor_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_monitoring_sessions');
    }
};
