<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('result_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('exam_sections')->onDelete('set null');
            $table->string('section_name');

            // Section performance
            $table->integer('total_questions');
            $table->integer('correct');
            $table->integer('incorrect');
            $table->integer('unanswered');
            $table->float('total_marks');
            $table->float('obtained_marks');
            $table->float('percentage');

            // Time metrics
            $table->integer('time_spent_seconds');
            $table->float('time_percentage'); // % of total time spent on this section

            // Accuracy
            $table->float('accuracy');

            $table->timestamps();

            $table->index(['result_id', 'section_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('result_sections');
    }
};
