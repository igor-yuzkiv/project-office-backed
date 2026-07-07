<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('updates the status and description', function () {
    $task = TaskModel::factory()->create([
        'project_id'  => $this->project->id,
        'status'      => TaskStatus::Open->value,
        'description' => 'Original description',
    ]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'status'      => TaskStatus::Closed->value,
        'description' => 'Updated description',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', TaskStatus::Closed->value)
        ->assertJsonPath('data.description', 'Updated description');
});

it('ignores fields other than status and description', function () {
    $task = TaskModel::factory()->create([
        'project_id' => $this->project->id,
        'name'       => 'Original name',
    ]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'name'   => 'Renamed task',
        'status' => TaskStatus::Closed->value,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Original name')
        ->assertJsonPath('data.status', TaskStatus::Closed->value);

    expect($task->fresh()->name)->toBe('Original name');
});
