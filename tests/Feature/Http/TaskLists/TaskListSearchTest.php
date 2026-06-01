<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('returns all task lists with pagination when query is empty', function () {
    TaskListModel::factory()->count(3)->create(['project_id' => $this->project->id]);

    $response = $this->postJson('/api/task-lists/search', ['query' => '']);

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);

    expect($response->json('meta.total'))->toBe(3);
});

it('returns task lists matching the search query', function () {
    TaskListModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Alpha List']);
    TaskListModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Beta List']);
    TaskListModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Gamma Work']);

    $response = $this->postJson('/api/task-lists/search', ['query' => 'List']);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

it('applies text filter on name field', function () {
    TaskListModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Alpha List']);
    TaskListModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Beta List']);

    $response = $this->postJson('/api/task-lists/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'text',
            'field_name' => 'name',
            'value'      => 'Alpha',
            'matchMode'  => 'contains',
            'params'     => [],
        ]],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(1)
        ->and($response->json('data.0.name'))->toBe('Alpha List');
});

it('applies text filter on project_id field', function () {
    $otherProject = ProjectModel::factory()->create();
    TaskListModel::factory()->count(2)->create(['project_id' => $this->project->id]);
    TaskListModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->postJson('/api/task-lists/search', [
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

it('returns 400 for unknown filter key', function () {
    $response = $this->postJson('/api/task-lists/search', [
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
    $response = $this->postJson('/api/task-lists/search', [
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
    TaskListModel::factory()->count(15)->create(['project_id' => $this->project->id]);

    $response = $this->postJson('/api/task-lists/search?per_page=5', ['query' => '']);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(15)
        ->and(count($response->json('data')))->toBe(5);
});

it('does not change GET /api/task-lists behavior', function () {
    TaskListModel::factory()->count(3)->create(['project_id' => $this->project->id]);

    $response = $this->getJson('/api/task-lists');

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);
});
