<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
});

it('returns all projects with pagination when query is empty', function () {
    ProjectModel::factory()->count(3)->create();

    $response = $this->postJson('/api/projects/search', ['query' => '']);

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);

    expect($response->json('meta.total'))->toBe(3);
});

it('returns projects matching the search query', function () {
    ProjectModel::factory()->create(['name' => 'Alpha Project']);
    ProjectModel::factory()->create(['name' => 'Beta Project']);
    ProjectModel::factory()->create(['name' => 'Gamma Work']);

    $response = $this->postJson('/api/projects/search', ['query' => 'Project']);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

it('applies text filter on name field', function () {
    ProjectModel::factory()->create(['name' => 'Alpha Project']);
    ProjectModel::factory()->create(['name' => 'Beta Project']);

    $response = $this->postJson('/api/projects/search', [
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
        ->and($response->json('data.0.name'))->toBe('Alpha Project');
});

it('applies text filter on prefix field', function () {
    ProjectModel::factory()->create(['name' => 'Alpha Project', 'prefix' => 'AP']);
    ProjectModel::factory()->create(['name' => 'Beta Project', 'prefix' => 'BP']);

    $response = $this->postJson('/api/projects/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'text',
            'field_name' => 'prefix',
            'value'      => 'AP',
            'matchMode'  => 'equals',
            'params'     => [],
        ]],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(1)
        ->and($response->json('data.0.prefix'))->toBe('AP');
});

it('returns 400 for unknown filter key', function () {
    $response = $this->postJson('/api/projects/search', [
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
    $response = $this->postJson('/api/projects/search', [
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
    ProjectModel::factory()->count(15)->create();

    $response = $this->postJson('/api/projects/search?per_page=5', ['query' => '']);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(15)
        ->and(count($response->json('data')))->toBe(5);
});

it('does not change GET /api/projects behavior', function () {
    ProjectModel::factory()->count(3)->create();

    $response = $this->getJson('/api/projects');

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);
});
