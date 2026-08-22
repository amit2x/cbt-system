<?php
// database/migrations/2026_06_24_081116_add_airport_id_to_all_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $tables = [
        'users',
        'courses',
        'batches',
        'batch_student',
        'exams',
        'exam_batches',
        'exam_logs',
        'exam_modules',
        'exam_questions',
        'exam_sections',
        'modules',
        'questions',
        'question_options',
        'question_category',
        'question_categories',
        'study_materials',
        'study_material_categories',
        'feedback',
        'feedback_responses',
        'analytics_reports',
        'student_exams',
        'student_answers',
        'results',
        'result_sections',
        'rank_lists',
        'student_activity_logs',
        'student_performance_tracking',
        'proctoring_images',
        'live_monitoring_sessions',
        'question_performance'
    ];

    public function up()
    {
        // Check if airports table exists, if not create it
        if (!Schema::hasTable('airports')) {
            $this->createAirportsTable();
        }

        // Get or create default airport (CCU - Kolkata)
        $defaultAirportId = $this->getOrCreateDefaultAirport();

        // Add airport_id to all tables if not exists
        $this->addAirportIdToTables($defaultAirportId);

        // Update unique constraints
        $this->updateUniqueConstraints();
    }

    protected function createAirportsTable()
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code', 10)->unique();
            $table->string('icao_code', 4)->nullable();
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

            $table->index(['status', 'code']);
            $table->index('domain');
            $table->index('country');
        });
    }

    protected function getOrCreateDefaultAirport()
    {
        // Check if CCU exists
        $airport = DB::table('airports')->where('code', 'CCU')->first();
        
        if ($airport) {
            return $airport->id;
        }

        // Create CCU airport
        $ccuId = DB::table('airports')->insertGetId([
            'name' => 'NSCBI Airport Kolkata',
            'code' => 'CCU',
            'icao_code' => 'VECC',
            'slug' => 'nscbi-airport-kolkata',
            'city' => 'Kolkata',
            'country' => 'India',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Check if IXC exists, if not create it
        if (!DB::table('airports')->where('code', 'IXC')->exists()) {
            DB::table('airports')->insert([
                'name' => 'Chandigarh Airport',
                'code' => 'IXC',
                'icao_code' => 'VICG',
                'slug' => 'chandigarh-airport',
                'city' => 'Chandigarh',
                'country' => 'India',
                'timezone' => 'Asia/Kolkata',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return $ccuId;
    }

    protected function addAirportIdToTables($defaultAirportId)
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            // Check if column already exists
            if (Schema::hasColumn($table, 'airport_id')) {
                // Update any NULL values to default
                DB::table($table)->whereNull('airport_id')->update(['airport_id' => $defaultAirportId]);
                continue;
            }

            // Add column as nullable first
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('airport_id')->after('id')->nullable();
            });

            // Update existing records
            DB::table($table)->update(['airport_id' => $defaultAirportId]);

            // Make column NOT NULL
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('airport_id')->nullable(false)->change();
            });

            // Add foreign key constraint if it doesn't exist
            $this->addForeignKeyIfNotExists($table);
        }
    }

    protected function addForeignKeyIfNotExists($table)
    {
        try {
            // Check if foreign key already exists
            $foreignKeyName = $table . '_airport_id_foreign';
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND CONSTRAINT_NAME = ?
            ", [$table, $foreignKeyName]);

            if (empty($constraints)) {
                // Check if any foreign key with airport_id exists
                $existingConstraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = ? 
                    AND COLUMN_NAME = 'airport_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ", [$table]);

                if (empty($existingConstraints)) {
                    // Add foreign key
                    Schema::table($table, function (Blueprint $table) {
                        $table->foreign('airport_id')
                              ->references('id')
                              ->on('airports')
                              ->onDelete('cascade');
                    });
                }
            }
        } catch (\Exception $e) {
            // Log error but continue
            echo "Could not add foreign key to $table: " . $e->getMessage() . "\n";
        }
    }

    protected function updateUniqueConstraints()
    {
        $constraints = [
            'users' => [
                'old' => 'users_email_unique',
                'new' => 'users_airport_email_unique',
                'columns' => ['airport_id', 'email']
            ],
            'courses' => [
                'old' => 'courses_slug_unique',
                'new' => 'courses_airport_slug_unique',
                'columns' => ['airport_id', 'slug']
            ],
            'exams' => [
                'old' => 'exams_slug_unique',
                'new' => 'exams_airport_slug_unique',
                'columns' => ['airport_id', 'slug']
            ],
            'batch_student' => [
                'old' => 'batch_student_batch_id_student_id_unique',
                'new' => 'batch_student_airport_unique',
                'columns' => ['airport_id', 'batch_id', 'student_id']
            ],
            'study_materials' => [
                'old' => 'study_materials_slug_unique',
                'new' => 'study_materials_airport_slug_unique',
                'columns' => ['airport_id', 'slug']
            ],
            'question_categories' => [
                'old' => 'question_categories_slug_unique',
                'new' => 'question_categories_airport_slug_unique',
                'columns' => ['airport_id', 'slug']
            ],
            'study_material_categories' => [
                'old' => 'study_material_categories_slug_unique',
                'new' => 'study_material_categories_airport_slug_unique',
                'columns' => ['airport_id', 'slug']
            ],
            'exam_batches' => [
                'old' => 'exam_batches_exam_id_batch_id_unique',
                'new' => 'exam_batches_airport_unique',
                'columns' => ['airport_id', 'exam_id', 'batch_id']
            ],
            'exam_modules' => [
                'old' => 'exam_modules_exam_id_module_id_unique',
                'new' => 'exam_modules_airport_unique',
                'columns' => ['airport_id', 'exam_id', 'module_id']
            ],
            'exam_questions' => [
                'old' => 'exam_questions_exam_id_question_id_unique',
                'new' => 'exam_questions_airport_unique',
                'columns' => ['airport_id', 'exam_id', 'question_id']
            ],
            'feedback_responses' => [
                'old' => 'feedback_responses_feedback_id_student_id_unique',
                'new' => 'feedback_responses_airport_unique',
                'columns' => ['airport_id', 'feedback_id', 'student_id']
            ],
            'student_answers' => [
                'old' => 'unique_student_question',
                'new' => 'student_answers_airport_unique',
                'columns' => ['airport_id', 'student_exam_id', 'question_id']
            ],
            'student_exams' => [
                'old' => 'unique_student_exam_attempt',
                'new' => 'student_exams_airport_unique',
                'columns' => ['airport_id', 'student_id', 'exam_id', 'batch_id']
            ],
            'question_category' => [
                'old' => 'question_category_unique',
                'new' => 'question_category_airport_unique',
                'columns' => ['airport_id', 'question_id', 'question_category_id']
            ]
        ];

        foreach ($constraints as $table => $constraint) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'airport_id')) {
                continue;
            }

            try {
                // Check if old constraint exists and drop it
                if ($this->constraintExists($table, $constraint['old'])) {
                    Schema::table($table, function (Blueprint $table) use ($constraint) {
                        $table->dropUnique($constraint['old']);
                    });
                }

                // Check if new constraint already exists
                if (!$this->constraintExists($table, $constraint['new'])) {
                    // Check if any other unique constraint exists on these columns
                    $existingConstraint = $this->findExistingUniqueConstraint($table, $constraint['columns']);
                    
                    if ($existingConstraint) {
                        // Drop the existing constraint
                        Schema::table($table, function (Blueprint $table) use ($existingConstraint) {
                            $table->dropUnique($existingConstraint);
                        });
                    }

                    // Add new unique constraint
                    Schema::table($table, function (Blueprint $table) use ($constraint) {
                        $table->unique($constraint['columns'], $constraint['new']);
                    });
                }
            } catch (\Exception $e) {
                echo "Could not update constraint for $table: " . $e->getMessage() . "\n";
            }
        }
    }

    protected function constraintExists($table, $constraintName)
    {
        try {
            $result = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND CONSTRAINT_NAME = ?
            ", [$table, $constraintName]);
            
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function findExistingUniqueConstraint($table, $columns)
    {
        try {
            $columnList = implode(',', $columns);
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME IN (" . implode(',', array_fill(0, count($columns), '?')) . ")
                AND CONSTRAINT_NAME LIKE '%unique%'
                GROUP BY CONSTRAINT_NAME
                HAVING COUNT(DISTINCT COLUMN_NAME) = ?
            ", array_merge([$table], $columns, [count($columns)]));

            return !empty($constraints) ? $constraints[0]->CONSTRAINT_NAME : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function dropForeignKeyIfExists($table, $foreignKey)
    {
        try {
            $result = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND CONSTRAINT_NAME = ?
            ", [$table, $foreignKey]);

            if (!empty($result)) {
                Schema::table($table, function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey);
                });
                return true;
            }
        } catch (\Exception $e) {
            // Foreign key might not exist
        }
        return false;
    }

    public function down()
    {
        // Remove unique constraints
        $constraints = [
            'users' => 'users_airport_email_unique',
            'courses' => 'courses_airport_slug_unique',
            'exams' => 'exams_airport_slug_unique',
            'batch_student' => 'batch_student_airport_unique',
            'study_materials' => 'study_materials_airport_slug_unique',
            'question_categories' => 'question_categories_airport_slug_unique',
            'study_material_categories' => 'study_material_categories_airport_slug_unique',
            'exam_batches' => 'exam_batches_airport_unique',
            'exam_modules' => 'exam_modules_airport_unique',
            'exam_questions' => 'exam_questions_airport_unique',
            'feedback_responses' => 'feedback_responses_airport_unique',
            'student_answers' => 'student_answers_airport_unique',
            'student_exams' => 'student_exams_airport_unique',
            'question_category' => 'question_category_airport_unique'
        ];

        foreach ($constraints as $table => $constraint) {
            if (Schema::hasTable($table)) {
                try {
                    if ($this->constraintExists($table, $constraint)) {
                        Schema::table($table, function (Blueprint $table) use ($constraint) {
                            $table->dropUnique($constraint);
                        });
                    }
                } catch (\Exception $e) {
                    // Constraint might not exist
                }
            }
        }

        // Drop foreign keys and airport_id column
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'airport_id')) {
                continue;
            }

            try {
                // Drop foreign key
                $foreignKeyName = $table . '_airport_id_foreign';
                if ($this->constraintExists($table, $foreignKeyName)) {
                    Schema::table($table, function (Blueprint $table) use ($foreignKeyName) {
                        $table->dropForeign($foreignKeyName);
                    });
                }

                // Drop column
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('airport_id');
                });
            } catch (\Exception $e) {
                echo "Could not remove airport_id from $table: " . $e->getMessage() . "\n";
            }
        }

        // Restore original unique constraints
        $originalConstraints = [
            'users' => ['email', 'users_email_unique'],
            'courses' => ['slug', 'courses_slug_unique'],
            'exams' => ['slug', 'exams_slug_unique'],
            'batch_student' => ['batch_id', 'student_id', 'batch_student_batch_id_student_id_unique'],
            'study_materials' => ['slug', 'study_materials_slug_unique'],
            'question_categories' => ['slug', 'question_categories_slug_unique'],
            'study_material_categories' => ['slug', 'study_material_categories_slug_unique'],
            'exam_batches' => ['exam_id', 'batch_id', 'exam_batches_exam_id_batch_id_unique'],
            'exam_modules' => ['exam_id', 'module_id', 'exam_modules_exam_id_module_id_unique'],
            'exam_questions' => ['exam_id', 'question_id', 'exam_questions_exam_id_question_id_unique'],
            'feedback_responses' => ['feedback_id', 'student_id', 'feedback_responses_feedback_id_student_id_unique'],
            'student_answers' => ['student_exam_id', 'question_id', 'unique_student_question'],
            'student_exams' => ['student_id', 'exam_id', 'batch_id', 'unique_student_exam_attempt'],
            'question_category' => ['question_id', 'question_category_id', 'question_category_unique']
        ];

        foreach ($originalConstraints as $table => $data) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            try {
                $columns = array_slice($data, 0, -1);
                $constraintName = end($data);
                
                if (!$this->constraintExists($table, $constraintName)) {
                    Schema::table($table, function (Blueprint $table) use ($columns, $constraintName) {
                        $table->unique($columns, $constraintName);
                    });
                }
            } catch (\Exception $e) {
                echo "Could not restore constraint for $table: " . $e->getMessage() . "\n";
            }
        }

        // Drop airports table if no data depends on it
        if (Schema::hasTable('airports')) {
            // Check if any data exists in other tables
            $hasData = false;
            foreach ($this->tables as $table) {
                if (Schema::hasTable($table) && DB::table($table)->count() > 0) {
                    $hasData = true;
                    break;
                }
            }

            if (!$hasData) {
                Schema::dropIfExists('airports');
            } else {
                echo "Warning: airports table still has data or is referenced by other tables\n";
            }
        }
    }
};