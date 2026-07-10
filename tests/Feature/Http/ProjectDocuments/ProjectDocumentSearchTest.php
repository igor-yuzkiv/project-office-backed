<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('returns all project documents with pagination when query is empty', function () {
    ProjectDocumentModel::factory()->count(3)->create(['project_id' => $this->project->id]);

    $response = $this->postJson('/api/project-documents/search', ['query' => '']);

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);

    expect($response->json('meta.total'))->toBe(3);
});

it('returns project documents matching the search query', function () {
    ProjectDocumentModel::factory()->create(['project_id' => $this->project->id, 'title' => 'Deployment Guide']);
    ProjectDocumentModel::factory()->create(['project_id' => $this->project->id, 'title' => 'Deployment Checklist']);
    ProjectDocumentModel::factory()->create(['project_id' => $this->project->id, 'title' => 'Onboarding']);

    $response = $this->postJson('/api/project-documents/search', ['query' => 'Deployment']);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

it('applies lookup filter on project_id field', function () {
    $otherProject = ProjectModel::factory()->create();
    ProjectDocumentModel::factory()->count(2)->create(['project_id' => $this->project->id]);
    ProjectDocumentModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->postJson('/api/project-documents/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'lookup',
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
    $response = $this->postJson('/api/project-documents/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'unknown',
            'field_name' => 'title',
            'value'      => 'foo',
            'matchMode'  => null,
            'params'     => [],
        ]],
    ]);

    $response->assertStatus(400)
        ->assertJsonStructure(['message', 'context']);
});

it('paginates search results', function () {
    ProjectDocumentModel::factory()->count(15)->create(['project_id' => $this->project->id]);

    $response = $this->postJson('/api/project-documents/search?per_page=5', ['query' => '']);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(15)
        ->and(count($response->json('data')))->toBe(5);
});
