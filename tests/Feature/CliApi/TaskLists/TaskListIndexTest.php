<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create(['prefix' => 'MTM']);
});

it('lists the project task lists ordered by sequence number', function () {
    TaskListModel::factory()->create([
        'project_id'      => $this->project->id,
        'key'             => 'MTM-TL-2',
        'sequence_number' => 2,
        'name'            => 'Second',
    ]);
    TaskListModel::factory()->create([
        'project_id'      => $this->project->id,
        'key'             => 'MTM-TL-1',
        'sequence_number' => 1,
        'name'            => 'First',
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/list");

    $response->assertOk()
        ->assertJsonPath('data.0.key', 'MTM-TL-1')
        ->assertJsonPath('data.1.key', 'MTM-TL-2');
});

it('paginates the list', function () {
    TaskListModel::factory()->count(3)->sequence(
        ['key' => 'MTM-TL-1', 'sequence_number' => 1],
        ['key' => 'MTM-TL-2', 'sequence_number' => 2],
        ['key' => 'MTM-TL-3', 'sequence_number' => 3],
    )->create(['project_id' => $this->project->id]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/list?per_page=2&page=2");

    $response->assertOk()
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('meta.current_page', 2);

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.key'))->toBe('MTM-TL-3');
});

it('does not list task lists of another project', function () {
    $otherProject = ProjectModel::factory()->create(['prefix' => 'OTH']);
    TaskListModel::factory()->create([
        'project_id'      => $otherProject->id,
        'key'             => 'OTH-TL-1',
        'sequence_number' => 1,
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/list");

    $response->assertOk();
    expect($response->json('data'))->toBe([]);
});

it('rejects an unauthenticated request', function () {
    auth()->forgetGuards();

    $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/list")
        ->assertUnauthorized();
});
