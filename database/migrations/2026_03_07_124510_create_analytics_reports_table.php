<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type'); // 'exam', 'student', 'batch', 'question', 'overall'
            $table->string('title');
            $table->json('filters')->nullable();
            $table->json('data');
            $table->json('summary')->nullable();
            $table->json('charts')->nullable();

            // Metadata
            $table->foreignId('generated_by')->constrained('users');
            $table->foreignId('exam_id')->nullable()->constrained();
            $table->foreignId('batch_id')->nullable()->constrained();
            $table->foreignId('student_id')->nullable()->constrained('users');

            // Date range
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();

            // File info (for exports)
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable(); // 'pdf', 'excel', 'csv'

            $table->timestamps();

            $table->index(['report_type', 'created_at']);
            $table->index(['generated_by', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_reports');
    }
};
