<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exam_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->integer('duration_minutes')->nullable(); // Null means use exam duration
            $table->integer('question_count')->default(0);
            $table->integer('total_marks')->default(0);
            $table->float('negative_marking')->nullable(); // Null means use exam negative marking
            $table->boolean('randomize_questions')->nullable(); // Null means use exam setting
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_sections');
    }
};
