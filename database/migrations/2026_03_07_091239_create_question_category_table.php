<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('question_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['question_id', 'question_category_id'], 'question_category_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('question_category');
    }
};
