<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('returns all tasks with pagination when query is empty', function () {
    TaskModel::factory()->count(3)->create(['project_id' => $this->project->id]);

    $response = $this->postJson('/api/tasks/search', ['query' => '']);

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);

    expect($response->json('meta.total'))->toBe(3);
});

it('returns tasks matching the search query', function () {
    TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Fix login bug']);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Fix signup bug']);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Add dashboard']);

    $response = $this->postJson('/api/tasks/search', ['query' => 'Fix']);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

it('applies text filter on name field', function () {
    TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Fix login bug']);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Add dashboard']);

    $response = $this->postJson('/api/tasks/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'text',
            'field_name' => 'name',
            'value'      => 'Fix',
            'matchMode'  => 'contains',
            'params'     => [],
        ]],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(1)
        ->and($response->json('data.0.name'))->toBe('Fix login bug');
});

it('applies text filter on project_id field', function () {
    $otherProject = ProjectModel::factory()->create();
    TaskModel::factory()->count(2)->create(['project_id' => $this->project->id]);
    TaskModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->postJson('/api/tasks/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'text',
            'field_name' => 'project_id',
            'value'      => $this->project->id,
            'matchMode'  => 'equals',
            'params'     => [],
        ]],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

it('applies integer filter on priority field', function () {
    TaskModel::factory()->create(['project_id' => $this->project->id, 'priority' => TaskPriority::High->value]);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'priority' => TaskPriority::Low->value]);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'priority' => TaskPriority::Medium->value]);

    $response = $this->postJson('/api/tasks/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'integer',
            'field_name' => 'priority',
            'value'      => TaskPriority::High->value,
            'matchMode'  => 'equals',
            'params'     => [],
        ]],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});

it('applies text filter on status field', function () {
    TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open->value]);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open->value]);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::Closed->value]);

    $response = $this->postJson('/api/tasks/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'text',
            'field_name' => 'status',
            'value'      => TaskStatus::Open->value,
            'matchMode'  => 'equals',
            'params'     => [],
        ]],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

it('returns 400 for unknown filter key', function () {
    $response = $this->postJson('/api/tasks/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'unknown',
            'field_name' => 'name',
            'value'      => 'foo',
            'matchMode'  => null,
            'params'     => [],
        ]],
    ]);

    $response->assertStatus(400)
        ->assertJsonStructure(['message', 'context']);
});

it('returns 400 for field not in allowed list', function () {
    $response = $this->postJson('/api/tasks/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'text',
            'field_name' => 'created_by',
            'value'      => 'foo',
            'matchMode'  => null,
            'params'     => [],
        ]],
    ]);

    $response->assertStatus(400)
        ->assertJsonStructure(['message', 'context']);
});

it('paginates search results', function () {
    TaskModel::factory()->count(15)->create(['project_id' => $this->project->id]);

    $response = $this->postJson('/api/tasks/search?per_page=5', ['query' => '']);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(15)
        ->and(count($response->json('data')))->toBe(5);
});

it('does not change GET /api/tasks behavior', function () {
    TaskModel::factory()->count(3)->create(['project_id' => $this->project->id]);

    $response = $this->getJson('/api/tasks');

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);
});
