<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
});

it('creates a task list', function () {
    $this->postJson('/api/task-lists', [
        'project_id'  => $this->project->id,
        'name'        => 'Audit Trail MVP',
        'description' => 'Seven tasks.',
    ])->assertCreated()->assertJsonPath('data.name', 'Audit Trail MVP');

    expect(TaskListModel::query()->sole()->name)->toBe('Audit Trail MVP');
});

it('records a task_list.created event', function () {
    $this->postJson('/api/task-lists', [
        'project_id'  => $this->project->id,
        'name'        => 'Audit Trail MVP',
        'description' => 'Seven tasks.',
    ])->assertCreated();

    $list = TaskListModel::query()->sole();
    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('task_list.created')
        ->and($record->title)->toBe("{$this->user->name} created list «Audit Trail MVP»")
        ->and($record->description)->toBe('Seven tasks.')
        ->and($record->subject_type)->toBe(TaskListModel::class)
        ->and($record->subject_id)->toBe($list->id);
});
