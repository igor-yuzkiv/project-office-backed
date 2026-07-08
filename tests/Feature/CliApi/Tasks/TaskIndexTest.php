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

it('returns a paginated list of tasks belonging to the project', function () {
    TaskModel::factory()->count(3)->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open->value]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/list");

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);

    expect($response->json('meta.total'))->toBe(3);
});

it('does not include tasks from other projects', function () {
    $otherProject = ProjectModel::factory()->create();
    TaskModel::factory()->count(2)->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open->value]);
    TaskModel::factory()->count(5)->create(['project_id' => $otherProject->id, 'status' => TaskStatus::Open->value]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/list");

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

it('excludes closed tasks', function () {
    TaskModel::factory()->count(2)->create([
        'project_id' => $this->project->id,
        'status'     => TaskStatus::Open->value,
    ]);
    TaskModel::factory()->count(3)->create([
        'project_id' => $this->project->id,
        'status'     => TaskStatus::Closed->value,
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/list");

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);

    $statuses = collect($response->json('data'))->pluck('status')->all();
    expect($statuses)->not->toContain(TaskStatus::Closed->value);
});

it('excludes backlog tasks', function () {
    TaskModel::factory()->count(2)->create([
        'project_id' => $this->project->id,
        'status'     => TaskStatus::Open->value,
    ]);
    TaskModel::factory()->count(3)->create([
        'project_id' => $this->project->id,
        'status'     => TaskStatus::Backlog->value,
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/list");

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);

    $statuses = collect($response->json('data'))->pluck('status')->all();
    expect($statuses)->not->toContain(TaskStatus::Backlog->value);
});
