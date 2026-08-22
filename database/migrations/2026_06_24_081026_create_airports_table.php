<?php
// database/migrations/2026_01_01_000000_create_airports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code', 10)->unique(); // IATA: CCU, IXC, etc.
            $table->string('icao_code', 4)->nullable(); // ICAO: VECC, VICG, etc.
            $table->string('slug', 255)->unique();
            $table->string('domain', 255)->nullable();
            $table->string('timezone', 100)->default('Asia/Kolkata');
            $table->string('country', 100)->default('India');
            $table->string('city', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('favicon', 255)->nullable();
            $table->json('settings')->nullable();
            $table->json('features')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'maintenance'])->default('active');
            $table->enum('subscription_plan', ['basic', 'standard', 'premium', 'enterprise'])->default('standard');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->integer('max_students')->default(1000);
            $table->integer('max_instructors')->default(100);
            $table->integer('max_exams')->default(500);
            $table->integer('storage_limit_gb')->default(10);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'code']);
            $table->index('domain');
            $table->index('country');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('airports');
    }
};