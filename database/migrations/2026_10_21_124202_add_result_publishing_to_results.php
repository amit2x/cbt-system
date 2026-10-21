<?php

// database/migrations/2026_10_21_add_result_publishing_to_results.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('results', function (Blueprint $table) {
            if (! Schema::hasColumn('results', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('status');
            }
            if (! Schema::hasColumn('results', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published');
            }
            if (! Schema::hasColumn('results', 'published_by')) {
                $table->unsignedBigInteger('published_by')->nullable()->after('published_at');
                $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('results', 'is_draft')) {
                $table->boolean('is_draft')->default(true)->after('is_published');
            }
            if (! Schema::hasColumn('results', 'result_publish_date')) {
                $table->timestamp('result_publish_date')->nullable()->after('published_by');
            }
            if (! Schema::hasColumn('results', 'moderation_notes')) {
                $table->text('moderation_notes')->nullable()->after('published_by');
            }
            if (! Schema::hasColumn('results', 'last_modified_by')) {
                $table->unsignedBigInteger('last_modified_by')->nullable()->after('moderation_notes');
            }
        });

        // Add published_at to exams as default auto-publish date
        Schema::table('exams', function (Blueprint $table) {
            if (! Schema::hasColumn('exams', 'result_publish_date')) {
                $table->timestamp('result_publish_date')->nullable()->after('show_result_immediately');
            }
        });
    }

    public function down()
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropColumn([
                'is_published', 'published_at', 'published_by',
                'is_draft', 'result_publish_date', 'moderation_notes',
                'last_modified_by',
            ]);
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('result_publish_date');
        });
    }
};
