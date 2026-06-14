<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('instructor_id')->constrained('users');
            $table->foreignId('batch_id')->constrained();
            $table->foreignId('course_id')->nullable()->constrained();
            $table->enum('type', ['quick', 'detailed'])->default('quick');
            $table->json('questions')->nullable();
            $table->enum('status', ['active', 'closed', 'expired'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedback');
    }
};
