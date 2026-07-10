<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('shows a document by id with the expected fields', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Architecture',
        'content'    => 'Some content',
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/docs/{$document->id}");

    $response->assertOk()
        ->assertExactJson(['data' => [
            'id'      => $document->id,
            'key'     => $document->key,
            'title'   => 'Architecture',
            'status'  => $document->status->value,
            'content' => 'Some content',
            'tags'    => [],
            'path'    => [[
                'id'    => $document->id,
                'key'   => $document->key,
                'title' => 'Architecture',
            ]],
        ]]);
});

it('shows a document by key', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id'      => $this->project->id,
        'key'             => 'DOC-MTM-7',
        'sequence_number' => 7,
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/docs/DOC-MTM-7");

    $response->assertOk()
        ->assertJsonPath('data.id', $document->id);
});

it('includes tags in the response', function () {
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $tag = TagModel::create(['name' => 'backend', 'color' => '#111111']);
    $document->tags()->attach([$tag->id]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/docs/{$document->id}");

    $response->assertOk()
        ->assertJsonPath('data.tags.0.name', 'backend');
});

it('returns the ancestor path root-first for a nested document', function () {
    $root = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Root',
    ]);
    $child = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'parent_id'  => $root->id,
        'title'      => 'Child',
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/docs/{$child->id}");

    $response->assertOk()
        ->assertJsonPath('data.path.0.id', $root->id)
        ->assertJsonPath('data.path.1.id', $child->id);
});

it('returns 404 for a document belonging to another project', function () {
    $otherProject = ProjectModel::factory()->create();
    $document = ProjectDocumentModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/docs/{$document->id}");

    $response->assertNotFound();
});
