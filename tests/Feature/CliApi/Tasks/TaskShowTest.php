<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('shows a task by id', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $task->id);
});

it('shows a task by key', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$task->key}");

    $response->assertOk()
        ->assertJsonPath('data.id', $task->id);
});

it('returns 404 for a task belonging to another project', function () {
    $otherProject = ProjectModel::factory()->create();
    $task = TaskModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}");

    $response->assertNotFound();
});
