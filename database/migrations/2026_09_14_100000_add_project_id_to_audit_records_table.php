<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_records', function (Blueprint $table) {
            // No foreign key: a record outlives its project the same way it outlives its subject.
            $table->ulid('project_id')->nullable()->after('subject_id')->index();
        });

        DB::table('audit_records')
            ->where('subject_type', ProjectModel::class)
            ->update(['project_id' => DB::raw('subject_id')]);

        // Subjects that were deleted before this column existed have no row to join and stay null.
        foreach ([TaskModel::class => 'tasks', TaskListModel::class => 'task_lists', ProjectDocumentModel::class => 'project_documents'] as $class => $table) {
            DB::statement(
                "UPDATE audit_records SET project_id = {$table}.project_id FROM {$table} WHERE audit_records.subject_type = ? AND audit_records.subject_id = {$table}.id",
                [$class],
            );
        }
    }

    public function down(): void
    {
        Schema::table('audit_records', function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
    }
};
