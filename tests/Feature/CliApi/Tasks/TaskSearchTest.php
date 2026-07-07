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

it('finds tasks belonging to the project', function () {
    TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Fix login bug']);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Add dashboard']);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/search", ['query' => 'Fix']);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});

it('does not leak tasks from other projects even when the query matches', function () {
    $otherProject = ProjectModel::factory()->create();
    TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Fix login bug']);
    TaskModel::factory()->create(['project_id' => $otherProject->id, 'name' => 'Fix login bug']);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/search", ['query' => 'Fix']);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});
