<?php
// database/migrations/2026_07_12_add_bilingual_and_explanation_image_to_questions.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Add bilingual fields and explanation image to questions
        Schema::table('questions', function (Blueprint $table) {
            // Hindi translations
            if (!Schema::hasColumn('questions', 'question_text_hi')) {
                $table->text('question_text_hi')->nullable()->after('question_text');
            }
            if (!Schema::hasColumn('questions', 'explanation_hi')) {
                $table->text('explanation_hi')->nullable()->after('explanation');
            }
            if (!Schema::hasColumn('questions', 'question_image_alt_hi')) {
                $table->string('question_image_alt_hi')->nullable()->after('question_image_alt');
            }
            
            // ✅ NEW: Explanation image column
            if (!Schema::hasColumn('questions', 'explanation_image')) {
                $table->string('explanation_image')->nullable()->after('question_image');
            }
            if (!Schema::hasColumn('questions', 'explanation_image_alt')) {
                $table->string('explanation_image_alt')->nullable()->after('explanation_image');
            }
            if (!Schema::hasColumn('questions', 'explanation_image_alt_hi')) {
                $table->string('explanation_image_alt_hi')->nullable()->after('explanation_image_alt');
            }
        });

        // 2. Add bilingual fields to question_options
        Schema::table('question_options', function (Blueprint $table) {
            if (!Schema::hasColumn('question_options', 'option_text_hi')) {
                $table->text('option_text_hi')->nullable()->after('option_text');
            }
            if (!Schema::hasColumn('question_options', 'option_image_alt_hi')) {
                $table->string('option_image_alt_hi')->nullable()->after('option_image_alt');
            }
        });

        // 3. Add preferred_locale to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'preferred_locale')) {
                $table->enum('preferred_locale', ['en', 'hi'])->default('en')->after('address');
            }
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'question_text_hi',
                'explanation_hi',
                'question_image_alt_hi',
                'explanation_image',
                'explanation_image_alt',
                'explanation_image_alt_hi'
            ]);
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->dropColumn([
                'option_text_hi',
                'option_image_alt_hi'
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferred_locale');
        });
    }
};