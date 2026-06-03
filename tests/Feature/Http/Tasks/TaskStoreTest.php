<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskPriority;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('creates a task without priority', function () {
    $response = $this->postJson('/api/tasks', [
        'project_id' => $this->project->id,
        'name'       => 'New Task',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Task')
        ->assertJsonPath('data.priority', null);
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
