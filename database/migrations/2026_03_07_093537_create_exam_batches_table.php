<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exam_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->datetime('available_from')->nullable();
            $table->datetime('available_until')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'batch_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_batches');
    }
};
