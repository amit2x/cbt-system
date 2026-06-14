<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feedback_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->json('answers')->nullable();
            $table->integer('rating')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['feedback_id', 'student_id']);
            $table->index(['feedback_id', 'submitted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedback_responses');
    }
};
