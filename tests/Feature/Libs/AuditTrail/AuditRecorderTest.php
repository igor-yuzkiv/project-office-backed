<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use App\Libs\AuditTrail\Facades\AuditTrail;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

function auditRecord(?Model $subject, string $title = 'Task renamed', string $type = 'task.updated'): AuditRecord
{
    return new class($subject, $title, $type) implements AuditRecord
    {
        public function __construct(
            private ?Model $subject,
            private string $title,
            private string $type,
        ) {}

        public function type(): string
        {
            return $this->type;
        }

        public function title(): string
        {
            return $this->title;
        }

        public function description(): ?string
        {
            return 'Renamed from A to B';
        }

        public function subject(): ?Model
        {
            return $this->subject;
        }
    };
}

function auditedTask(): TaskModel
{
    return TaskModel::factory()->create(['project_id' => ProjectModel::factory()->create()->id]);
}

it('writes a row with id, created_at and the authenticated user', function () {
    $user = UserModel::factory()->create();
    $this->actingAs($user);
    $task = auditedTask();

    AuditTrail::capture(auditRecord($task));

    $record = AuditRecordModel::query()->sole();

    expect($record->id)->not->toBeEmpty()
        ->and($record->created_at)->not->toBeNull()
        ->and($record->created_by)->toBe($user->id)
        ->and($record->type)->toBe('task.updated');
});

it('writes created_by as null without authentication', function () {
    AuditTrail::capture(auditRecord(null));

    expect(AuditRecordModel::query()->sole()->created_by)->toBeNull();
});

it('stores a record without a subject', function () {
    AuditTrail::capture(auditRecord(null));

    $record = AuditRecordModel::query()->sole();

    expect($record->subject_type)->toBeNull()
        ->and($record->subject_id)->toBeNull();
});

it('stores the subject class and key, and resolves back to the same model', function () {
    $task = auditedTask();

    AuditTrail::capture(auditRecord($task));

    $record = AuditRecordModel::query()->sole();

    expect($record->subject_type)->toBe(TaskModel::class)
        ->and($record->subject_id)->toBe($task->id)
        ->and($record->subject)->toBeInstanceOf(TaskModel::class)
        ->and($record->subject->id)->toBe($task->id);
});

it('stores a title of a thousand characters', function () {
    $title = str_repeat('a', 1000);

    AuditTrail::capture(auditRecord(null, $title));

    expect(AuditRecordModel::query()->sole()->title)->toBe($title);
});

it('logs a warning instead of throwing when the write fails', function () {
    $type = str_repeat('t', 300);

    Log::shouldReceive('warning')->once()->withArgs(
        fn (string $message, array $context) => $context['type'] === $type
    );

    // A type longer than the column allows makes the INSERT fail inside capture().
    AuditTrail::capture(auditRecord(null, 'Title', $type));
})->throwsNoExceptions();

it('carries the subject in the warning context', function () {
    $task = auditedTask();
    $type = str_repeat('t', 300);

    Log::shouldReceive('warning')->once()->withArgs(
        fn (string $message, array $context) => $context['subject'] === ['type' => TaskModel::class, 'id' => $task->id]
    );

    AuditTrail::capture(auditRecord($task, 'Title', $type));
})->throwsNoExceptions();

it('leaves the surrounding transaction writable after a failed capture', function () {
    DB::beginTransaction();
    $levelBefore = DB::transactionLevel();

    AuditTrail::capture(auditRecord(null, 'Title', str_repeat('t', 300)));

    // Without the savepoint rollback inside capture(), Postgres would refuse this write:
    // the transaction would still be in the aborted state left by the failed INSERT.
    $user = UserModel::factory()->create();

    expect(DB::transactionLevel())->toBe($levelBefore)
        ->and(UserModel::query()->whereKey($user->id)->exists())->toBeTrue();

    DB::commit();
});

it('ignores a subject that has no key', function () {
    AuditTrail::capture(auditRecord(new TaskModel));

    $record = AuditRecordModel::query()->sole();

    expect($record->subject_type)->toBeNull()
        ->and($record->subject_id)->toBeNull();
});

it('can be spied on in a test', function () {
    AuditTrail::spy();

    $record = auditRecord(null);
    AuditTrail::capture($record);

    AuditTrail::shouldHaveReceived('capture')->once()->with($record);
});

it('stores the project of a task, a task list and a document subject', function () {
    $project = ProjectModel::factory()->create();
    $task = TaskModel::factory()->create(['project_id' => $project->id]);
    $list = TaskListModel::factory()->create(['project_id' => $project->id]);
    $document = ProjectDocumentModel::factory()->create(['project_id' => $project->id]);

    AuditTrail::capture(auditRecord($task));
    AuditTrail::capture(auditRecord($list));
    AuditTrail::capture(auditRecord($document));

    expect(AuditRecordModel::query()->pluck('project_id')->all())->toBe([$project->id, $project->id, $project->id]);
});

it('stores a project subject as its own project', function () {
    $project = ProjectModel::factory()->create();

    AuditTrail::capture(auditRecord($project));

    expect(AuditRecordModel::query()->sole()->project_id)->toBe($project->id);
});

it('stores no project without a subject', function () {
    AuditTrail::capture(auditRecord(null));

    expect(AuditRecordModel::query()->sole()->project_id)->toBeNull();
});
