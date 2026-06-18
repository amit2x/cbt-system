<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'module_id')) {
                $table->foreignId('module_id')->nullable()->after('course_id')
                    ->constrained('modules')
                    ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'module_id')) {
                $table->dropForeign(['module_id']);
                $table->dropColumn('module_id');
            }
        });
    }
};