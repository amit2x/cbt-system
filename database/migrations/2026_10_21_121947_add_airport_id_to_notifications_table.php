<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Adds an unsigned big integer column named airport_id with a default value of 1
            // It is placed after the 'id' column for clean table structure
            $table->unsignedBigInteger('airport_id')->default(1)->after('id');

            // Optional: If you have an 'airports' table, uncomment the line below to add a foreign key constraint
            // $table->foreign('airport_id')->references('id')->on('airports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drops the column if the migration is rolled back
            $table->dropColumn('airport_id');
        });
    }
};
