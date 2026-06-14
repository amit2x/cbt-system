<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->text('explanation')->nullable();
            $table->enum('type', ['single_choice', 'multiple_choice', 'numerical'])->default('single_choice');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->integer('marks')->default(1);
            $table->float('negative_marks')->default(0);
            $table->json('tags')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained('modules');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for faster search
            $table->index('type');
            $table->index('difficulty');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
};
