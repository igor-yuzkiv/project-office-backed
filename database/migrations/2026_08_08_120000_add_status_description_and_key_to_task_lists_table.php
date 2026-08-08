<?php

use App\Domains\TaskList\Enums\TaskListStatus;
use Database\Backfills\TaskListKeyBackfill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_lists', function (Blueprint $table) {
            $table->string('key')->nullable()->after('project_id');
            $table->unsignedInteger('sequence_number')->nullable()->after('key');
            $table->string('status', 100)->default(TaskListStatus::Open->value)->after('name');
            $table->longText('description')->nullable()->after('status');
        });

        (new TaskListKeyBackfill)->run();

        // Postgres runs DDL inside the transaction Laravel wraps this migration in, so the
        // nullable window above never becomes a state of the schema: either all three steps
        // apply, or none of them do.
        Schema::table('task_lists', function (Blueprint $table) {
            $table->string('key')->nullable(false)->change();
            $table->unsignedInteger('sequence_number')->nullable(false)->change();

            $table->unique('key');
            $table->unique(['project_id', 'sequence_number']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('task_lists', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropUnique(['project_id', 'sequence_number']);
            $table->dropUnique(['key']);

            $table->dropColumn(['key', 'sequence_number', 'status', 'description']);
        });
    }
};
