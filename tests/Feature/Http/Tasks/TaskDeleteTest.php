<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->task = TaskModel::factory()->create([
        'project_id'  => ProjectModel::factory()->create()->id,
        'description' => 'Original body.',
    ]);
});

it('deletes the task', function () {
    $this->deleteJson("/api/tasks/{$this->task->id}")->assertOk();

    expect(TaskModel::query()->whereKey($this->task->id)->exists())->toBeFalse();
});

it('records a task.deleted audit event that keeps pointing at the deleted task', function () {
    $this->deleteJson("/api/tasks/{$this->task->id}")->assertOk();

    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('task.deleted')
        ->and($record->subject_type)->toBe(TaskModel::class)
        ->and($record->subject_id)->toBe($this->task->id)
        ->and($record->title)->toEndWith("deleted {$this->task->key}")
        ->and($record->description)->toBe('Original body.');
});
