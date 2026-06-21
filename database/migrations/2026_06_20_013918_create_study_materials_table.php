<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('study_materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable(); // For rich text content
            $table->string('file_path')->nullable(); // For PDF/File uploads
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            
            // Relationships
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('study_material_categories')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Material type
            $table->enum('material_type', ['pdf', 'document', 'video', 'link', 'content'])->default('document');
            $table->string('external_url')->nullable(); // For external links
            
            // Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->integer('views')->default(0);
            $table->integer('downloads')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['course_id', 'module_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('study_materials');
    }
};