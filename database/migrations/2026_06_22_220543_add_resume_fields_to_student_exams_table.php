<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_exams', function (Blueprint $table) {

            $table->unsignedBigInteger('current_question_id')
                  ->nullable()
                  ->after('status');

            $table->boolean('is_saved_exit')
                  ->default(false)
                  ->after('current_question_id');

            $table->timestamp('saved_at')
                  ->nullable()
                  ->after('is_saved_exit');

            $table->foreign('current_question_id')
                  ->references('id')
                  ->on('questions')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_exams', function (Blueprint $table) {

            $table->dropForeign(['current_question_id']);

            $table->dropColumn([
                'current_question_id',
                'is_saved_exit',
                'saved_at'
            ]);
        });
    }
};