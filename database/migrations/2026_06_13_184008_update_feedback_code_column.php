<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('feedback', function (Blueprint $table) {
            // Make code nullable or add a default value
            if (Schema::hasColumn('feedback', 'code')) {
                $table->string('code')->nullable()->change();
            } else {
                $table->string('code')->nullable()->after('id');
            }
        });
    }

    public function down()
    {
        Schema::table('feedback', function (Blueprint $table) {
            if (Schema::hasColumn('feedback', 'code')) {
                $table->string('code')->nullable(false)->change();
            }
        });
    }
};
