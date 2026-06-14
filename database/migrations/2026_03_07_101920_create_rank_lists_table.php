<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rank_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->date('generated_date');

            // Rank list data
            $table->json('rank_data'); // Stores student_id, rank, score, etc.
            $table->integer('total_students');
            $table->float('average_score');
            $table->float('highest_score');
            $table->float('lowest_score');
            $table->float('median_score');

            // Statistics
            $table->json('score_distribution')->nullable();
            $table->json('percentiles')->nullable();

            // Metadata
            $table->foreignId('generated_by')->constrained('users');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index(['exam_id', 'batch_id']);
            $table->index(['generated_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rank_lists');
    }
};
