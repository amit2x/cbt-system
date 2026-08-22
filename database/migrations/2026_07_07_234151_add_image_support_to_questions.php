<?php
// database/migrations/2026_07_07_000000_add_image_support_to_questions.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('question_image')->nullable()->after('question_text');
            $table->string('question_image_alt')->nullable()->after('question_image');
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->string('option_image')->nullable()->after('option_text');
            $table->string('option_image_alt')->nullable()->after('option_image');
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['question_image', 'question_image_alt']);
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->dropColumn(['option_image', 'option_image_alt']);
        });
    }
};