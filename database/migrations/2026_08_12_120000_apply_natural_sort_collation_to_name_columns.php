<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Names carry a numbering pattern — "1. Subject", "2. Subject" — and the plan is read in that
 * order. The database default collation (en_US.utf8) ignores punctuation at the primary level,
 * so "1. Data model" compares as "1Datamodel" and loses to "10. Documentation": the whole second
 * decade jumps to the front of every list sorted by name.
 *
 * An ICU collation with numeric ordering compares runs of digits as numbers. It is deterministic,
 * so equality and LIKE keep byte semantics — only ordering changes. Applying it to the column
 * rather than to each query means every ORDER BY name is correct without the caller knowing.
 */
return new class extends Migration
{
    /** @var array<string, string> table => column */
    private const COLUMNS = [
        'tasks'      => 'name',
        'task_lists' => 'name',
        'projects'   => 'name',
    ];

    public function up(): void
    {
        // migrate:fresh drops tables but not collations, so a second run would fail on a
        // duplicate object without this guard.
        DB::statement("CREATE COLLATION IF NOT EXISTS natural_sort (provider = icu, locale = 'en-u-kn-true')");

        foreach (self::COLUMNS as $table => $column) {
            DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE varchar(255) COLLATE natural_sort");
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $table => $column) {
            DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE varchar(255) COLLATE \"default\"");
        }

        DB::statement('DROP COLLATION IF EXISTS natural_sort');
    }
};
