<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('exam_sections')->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->integer('marks')->nullable(); // Override question marks
            $table->float('negative_marks')->nullable(); // Override negative marks
            $table->integer('time_limit_seconds')->nullable(); // Per question time limit
            $table->timestamps();

            $table->unique(['exam_id', 'question_id']);
            $table->index(['exam_id', 'section_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_questions');
    }
};
