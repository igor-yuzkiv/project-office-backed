<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('creates a task using the project id from the route', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name' => 'New Task',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Task')
        ->assertJsonPath('data.project_id', $this->project->id);
});

it('ignores a project_id provided in the request body', function () {
    $otherProject = ProjectModel::factory()->create();

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name'       => 'New Task',
        'project_id' => $otherProject->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.project_id', $this->project->id);
});
