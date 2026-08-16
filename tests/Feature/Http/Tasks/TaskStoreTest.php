<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
});

it('creates a task with the default None priority when none is provided', function () {
    $response = $this->postJson('/api/tasks', [
        'project_id' => $this->project->id,
        'name'       => 'New Task',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Task')
        ->assertJsonPath('data.priority.value', TaskPriority::None->value)
        ->assertJsonPath('data.priority.name', TaskPriority::None->name);
});

it('creates a task with priority', function () {
    $response = $this->postJson('/api/tasks', [
        'project_id' => $this->project->id,
        'name'       => 'New Task',
        'priority'   => TaskPriority::High->value,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.priority.value', TaskPriority::High->value)
        ->assertJsonPath('data.priority.name', TaskPriority::High->name);
});

it('rejects an invalid priority value', function () {
    $response = $this->postJson('/api/tasks', [
        'project_id' => $this->project->id,
        'name'       => 'New Task',
        'priority'   => 999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['priority']);
});

it('records a task.created audit event', function () {
    $this->postJson('/api/tasks', [
        'project_id' => $this->project->id,
        'name'       => 'New Task',
    ])->assertCreated();

    $task = TaskModel::query()->sole();
    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('task.created')
        ->and($record->subject_type)->toBe(TaskModel::class)
        ->and($record->subject_id)->toBe($task->id)
        ->and($record->title)->toBe("{$this->user->name} created {$task->key}")
        ->and($record->description)->toBe('New Task');
});
