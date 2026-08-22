<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Upgrade column type to support large exam structures (up to 4GB text blocks)
        Schema::table('cache', function (Blueprint $table) {
            $table->longText('value')->change();
        });

        // 2. Add high-speed index safely if it doesn't already exist
        $this->addIndexIfNotExist('cache', 'key', 'cache_key_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cache', function (Blueprint $table) {
            $table->text('value')->change();
            
            // Drop index on rollback if it exists
            if ($this->hasIndex('cache', 'cache_key_index')) {
                $table->dropIndex('cache_key_index');
            }
        });
    }

    /**
     * Helper method to safely verify index existence and inject high-speed key lookup
     */
    private function addIndexIfNotExist(string $table, string $column, string $indexName): void
    {
        if (!$this->hasIndex($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                // Laravel Cache keys can be long; we explicitly index the first 191 characters for MySQL speed
                $table->index([DB::raw("`{$column}`(191)")], $indexName);
            });
        }
    }

    /**
     * Native MySQL schema check to see if an index name is registered
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $conn = Schema::getConnection();
        $dbName = $conn->getDatabaseName();
        $prefix = $conn->getTablePrefix();
        $fullName = $prefix . $table;

        $result = DB::select("
            SELECT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND INDEX_NAME = ?
        ", [$dbName, $fullName, $indexName]);

        return count($result) > 0;
    }
};
