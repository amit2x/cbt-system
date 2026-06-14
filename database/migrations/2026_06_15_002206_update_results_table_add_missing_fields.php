<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('results', function (Blueprint $table) {
            // Add missing total_questions field if not exists
            if (! Schema::hasColumn('results', 'total_questions')) {
                $table->integer('total_questions')->default(0)->after('exam_id');
            }

            // Make sure other fields have default values
            if (Schema::hasColumn('results', 'total_marks')) {
                $table->integer('total_marks')->default(0)->change();
            }
            if (Schema::hasColumn('results', 'obtained_marks')) {
                $table->float('obtained_marks')->default(0)->change();
            }
            if (Schema::hasColumn('results', 'percentage')) {
                $table->float('percentage')->default(0)->change();
            }
            if (Schema::hasColumn('results', 'correct_answers')) {
                $table->integer('correct_answers')->default(0)->change();
            }
            if (Schema::hasColumn('results', 'incorrect_answers')) {
                $table->integer('incorrect_answers')->default(0)->change();
            }
            if (Schema::hasColumn('results', 'unanswered')) {
                $table->integer('unanswered')->default(0)->change();
            }
        });
    }

    public function down()
    {
        Schema::table('results', function (Blueprint $table) {
            if (Schema::hasColumn('results', 'total_questions')) {
                $table->dropColumn('total_questions');
            }
        });
    }
};
