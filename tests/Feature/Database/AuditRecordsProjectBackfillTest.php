<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * Replays the migration over rows written before the column existed: the rows are created first,
 * the column is dropped from under them, and up() is run again.
 */
function backfillProjectIds(): void
{
    Schema::table('audit_records', fn ($table) => $table->dropColumn('project_id'));

    $migration = require database_path('migrations/2026_09_14_100000_add_project_id_to_audit_records_table.php');
    $migration->up();
}

function recordWithoutProject(?Model $subject): AuditRecordModel
{
    $factory = $subject === null ? AuditRecordModel::factory() : AuditRecordModel::factory()->forSubject($subject);

    return $factory->create(['project_id' => null]);
}

it('fills project_id from each subject type', function () {
    $project = ProjectModel::factory()->create();
    $task = recordWithoutProject(TaskModel::factory()->create(['project_id' => $project->id]));
    $list = recordWithoutProject(TaskListModel::factory()->create(['project_id' => $project->id]));
    $document = recordWithoutProject(ProjectDocumentModel::factory()->create(['project_id' => $project->id]));
    $ownProject = recordWithoutProject($project);

    backfillProjectIds();

    foreach ([$task, $list, $document, $ownProject] as $record) {
        expect($record->fresh()->project_id)->toBe($project->id);
    }
});

it('leaves a deleted subject and a record without a subject at null', function () {
    $orphan = AuditRecordModel::factory()->create([
        'subject_type' => TaskModel::class,
        'subject_id'   => '01JZZZZZZZZZZZZZZZZZZZZZZZ',
        'project_id'   => null,
    ]);
    $bare = recordWithoutProject(null);

    backfillProjectIds();

    expect($orphan->fresh()->project_id)->toBeNull()
        ->and($bare->fresh()->project_id)->toBeNull();
});
