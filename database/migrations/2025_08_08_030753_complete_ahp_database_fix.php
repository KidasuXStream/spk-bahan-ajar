<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, check if table exists
        if (!Schema::hasTable('ahp_comparisons')) {
            // Create the table if it doesn't exist
            Schema::create('ahp_comparisons', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ahp_session_id');
                $table->unsignedBigInteger('kriteria_1_id');
                $table->unsignedBigInteger('kriteria_2_id');
                $table->decimal('nilai', 8, 4)->default(1.0000);
                $table->timestamps();

                // Foreign key constraints
                $table->foreign('ahp_session_id')->references('id')->on('ahp_sessions')->onDelete('cascade');
                $table->foreign('kriteria_1_id')->references('id')->on('kriterias')->onDelete('cascade');
                $table->foreign('kriteria_2_id')->references('id')->on('kriterias')->onDelete('cascade');

                // Unique constraint
                $table->unique(['ahp_session_id', 'kriteria_1_id', 'kriteria_2_id'], 'unique_session_criteria_pair');

                // Indexes
                $table->index(['ahp_session_id'], 'idx_ahp_session');
            });

            Log::info('AHP Comparisons table created successfully');
        } else {
            // Modify existing table
            try {
                Schema::table('ahp_comparisons', function (Blueprint $table) {
                    // Check and add columns if needed
                    if (!Schema::hasColumn('ahp_comparisons', 'kriteria_1_id')) {
                        $table->unsignedBigInteger('kriteria_1_id')->after('ahp_session_id');
                    }

                    if (!Schema::hasColumn('ahp_comparisons', 'kriteria_2_id')) {
                        $table->unsignedBigInteger('kriteria_2_id')->after('kriteria_1_id');
                    }
                });

                // Modify nilai column separately to avoid conflicts
                if (Schema::hasColumn('ahp_comparisons', 'nilai')) {
                    DB::statement('ALTER TABLE ahp_comparisons MODIFY COLUMN nilai DECIMAL(8,4) DEFAULT 1.0000');
                } else {
                    Schema::table('ahp_comparisons', function (Blueprint $table) {
                        $table->decimal('nilai', 8, 4)->default(1.0000);
                    });
                }

                Log::info('AHP Comparisons table modified successfully');
            } catch (\Exception $e) {
                Log::warning('Could not modify table structure: ' . $e->getMessage());
            }

            // Add foreign keys if they don't exist
            $this->addForeignKeysIfNotExist();

            // Add unique constraint if it doesn't exist
            $this->addUniqueConstraintIfNotExist();
        }

        // Update any existing NULL values to default
        try {
            DB::statement('UPDATE ahp_comparisons SET nilai = 1.0000 WHERE nilai IS NULL');
            Log::info('Updated NULL values to default');
        } catch (\Exception $e) {
            Log::warning('Could not update NULL values: ' . $e->getMessage());
        }

        // Verify the table structure
        $this->verifyTableStructure();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE ahp_comparisons MODIFY COLUMN nilai DECIMAL(8,4)');
            Log::info('Removed default value from nilai column');
        } catch (\Exception $e) {
            Log::warning('Could not remove default value: ' . $e->getMessage());
        }
    }

    /**
     * Add foreign keys if they don't exist
     */
    private function addForeignKeysIfNotExist(): void
    {
        $foreignKeys = $this->getForeignKeys('ahp_comparisons');

        if (!in_array('ahp_comparisons_ahp_session_id_foreign', $foreignKeys)) {
            try {
                Schema::table('ahp_comparisons', function (Blueprint $table) {
                    $table->foreign('ahp_session_id')->references('id')->on('ahp_sessions')->onDelete('cascade');
                });
                Log::info('Added foreign key: ahp_session_id');
            } catch (\Exception $e) {
                Log::warning('Could not add foreign key ahp_session_id: ' . $e->getMessage());
            }
        }

        if (!in_array('ahp_comparisons_kriteria_1_id_foreign', $foreignKeys)) {
            try {
                Schema::table('ahp_comparisons', function (Blueprint $table) {
                    $table->foreign('kriteria_1_id')->references('id')->on('kriterias')->onDelete('cascade');
                });
                Log::info('Added foreign key: kriteria_1_id');
            } catch (\Exception $e) {
                Log::warning('Could not add foreign key kriteria_1_id: ' . $e->getMessage());
            }
        }

        if (!in_array('ahp_comparisons_kriteria_2_id_foreign', $foreignKeys)) {
            try {
                Schema::table('ahp_comparisons', function (Blueprint $table) {
                    $table->foreign('kriteria_2_id')->references('id')->on('kriterias')->onDelete('cascade');
                });
                Log::info('Added foreign key: kriteria_2_id');
            } catch (\Exception $e) {
                Log::warning('Could not add foreign key kriteria_2_id: ' . $e->getMessage());
            }
        }
    }

    /**
     * Add unique constraint if it doesn't exist
     */
    private function addUniqueConstraintIfNotExist(): void
    {
        $indexes = $this->getIndexes('ahp_comparisons');
        if (!in_array('unique_session_criteria_pair', $indexes)) {
            try {
                Schema::table('ahp_comparisons', function (Blueprint $table) {
                    $table->unique(['ahp_session_id', 'kriteria_1_id', 'kriteria_2_id'], 'unique_session_criteria_pair');
                });
                Log::info('Added unique constraint: unique_session_criteria_pair');
            } catch (\Exception $e) {
                Log::warning('Could not add unique constraint: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get foreign keys for a table
     */
    private function getForeignKeys(string $tableName): array
    {
        try {
            $database = config('database.connections.mysql.database');

            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$database, $tableName]);

            return array_column($foreignKeys, 'CONSTRAINT_NAME');
        } catch (\Exception $e) {
            Log::warning('Could not get foreign keys: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get indexes for a table
     */
    private function getIndexes(string $tableName): array
    {
        try {
            $database = config('database.connections.mysql.database');

            $indexes = DB::select("
                SELECT INDEX_NAME
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
            ", [$database, $tableName]);

            return array_column($indexes, 'INDEX_NAME');
        } catch (\Exception $e) {
            Log::warning('Could not get indexes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Verify table structure is correct
     */
    private function verifyTableStructure(): void
    {
        try {
            $database = config('database.connections.mysql.database');

            // Check required columns exist
            $columns = DB::select("
                SELECT COLUMN_NAME, COLUMN_DEFAULT, IS_NULLABLE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = 'ahp_comparisons'
            ", [$database]);

            $columnNames = array_column($columns, 'COLUMN_NAME');
            $requiredColumns = ['id', 'ahp_session_id', 'kriteria_1_id', 'kriteria_2_id', 'nilai', 'created_at', 'updated_at'];

            $missingColumns = array_diff($requiredColumns, $columnNames);
            if (!empty($missingColumns)) {
                Log::error('Missing required columns: ' . implode(', ', $missingColumns));
                throw new \Exception("Required columns not found: " . implode(', ', $missingColumns));
            }

            // Check nilai column has default value
            $nilaiColumn = collect($columns)->firstWhere('COLUMN_NAME', 'nilai');
            if (!$nilaiColumn || $nilaiColumn->COLUMN_DEFAULT === null) {
                Log::warning('Column nilai does not have default value set');
            } else {
                Log::info('Column nilai has default value: ' . $nilaiColumn->COLUMN_DEFAULT);
            }

            Log::info('AHP Comparisons table structure verified successfully', [
                'columns' => $columnNames,
                'nilai_default' => $nilaiColumn->COLUMN_DEFAULT ?? 'NULL'
            ]);
        } catch (\Exception $e) {
            Log::error('Table structure verification failed: ' . $e->getMessage());
            // Don't throw, just log the error
        }
    }
};
