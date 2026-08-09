<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create(['prefix' => 'MTM']);
});

it('assigns a key and the default status when a task list is created', function () {
    $response = $this->postJson('/api/task-lists', [
        'project_id' => $this->project->id,
        'name'       => 'Backlog',
    ]);

    $response->assertCreated();

    $taskList = TaskListModel::query()->firstOrFail();

    expect($taskList->key)->toBe('MTM-TL-1')
        ->and($taskList->sequence_number)->toBe(1)
        ->and($taskList->status)->toBe(TaskListStatus::Open);
});

it('assigns sequential keys within a project', function () {
    $this->postJson('/api/task-lists', ['project_id' => $this->project->id, 'name' => 'First'])->assertCreated();
    $this->postJson('/api/task-lists', ['project_id' => $this->project->id, 'name' => 'Second'])->assertCreated();

    expect(TaskListModel::query()->orderBy('sequence_number')->pluck('key')->all())
        ->toBe(['MTM-TL-1', 'MTM-TL-2']);
});

it('resolves a task list by its key as well as by its ulid', function () {
    $taskList = TaskListModel::factory()->create([
        'project_id'      => $this->project->id,
        'key'             => 'MTM-TL-7',
        'sequence_number' => 7,
    ]);

    $byUlid = $this->getJson("/api/task-lists/{$taskList->id}");
    $byKey = $this->getJson('/api/task-lists/MTM-TL-7');

    $byUlid->assertOk();
    $byKey->assertOk();

    expect($byKey->json('data.id'))->toBe($taskList->id);
});
