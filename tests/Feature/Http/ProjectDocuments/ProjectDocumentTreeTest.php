<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('returns only root-level documents by default', function () {
    $root = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
    ProjectDocumentModel::factory()->for($this->project, 'project')->create(['parent_id' => $root->id]);

    $response = $this->getJson("/api/projects/{$this->project->id}/project-documents/tree");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $root->id)
        ->assertJsonPath('data.0.has_children', true);
});

it('returns children of a given parent_id', function () {
    $root = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
    $child = ProjectDocumentModel::factory()->for($this->project, 'project')->create(['parent_id' => $root->id]);

    $response = $this->getJson("/api/projects/{$this->project->id}/project-documents/tree?parent_id={$root->id}");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $child->id)
        ->assertJsonPath('data.0.has_children', false);
});

it('does not mix documents from other projects', function () {
    $otherProject = ProjectModel::factory()->create();
    $mine = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
    ProjectDocumentModel::factory()->for($otherProject, 'project')->create();

    $response = $this->getJson("/api/projects/{$this->project->id}/project-documents/tree");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $mine->id);
});

it('includes tags and updated_by, and paginates root documents', function () {
    $tag = TagModel::create(['name' => 'tree', 'color' => '#555555']);
    $document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
    $document->tags()->sync([$tag->id]);

    $response = $this->getJson("/api/projects/{$this->project->id}/project-documents/tree?per_page=1");

    $response->assertOk()
        ->assertJsonPath('data.0.tags.0.id', $tag->id)
        ->assertJsonPath('data.0.updated_by.id', $document->updated_by)
        ->assertJsonPath('meta.per_page', 1);
});

it('filters documents linked to a given task', function () {
    $linked = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
    ProjectDocumentModel::factory()->for($this->project, 'project')->create();
    $task = TaskModel::factory()->for($this->project, 'project')->create();
    $linked->tasks()->attach($task);

    $response = $this->getJson(
        "/api/projects/{$this->project->id}/project-documents/tree?".http_build_query([
            'filters' => [['filter_key' => 'tasks', 'value' => [$task->id]]],
        ])
    );

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $linked->id);
});

it('rejects a non-existent project', function () {
    $response = $this->getJson('/api/projects/'.((string) Str::ulid()).'/project-documents/tree');

    $response->assertNotFound();
});

it('returns no results when parent_id belongs to a different project', function () {
    $otherProject = ProjectModel::factory()->create();
    $foreignDocument = ProjectDocumentModel::factory()->for($otherProject, 'project')->create();

    $response = $this->getJson(
        "/api/projects/{$this->project->id}/project-documents/tree?parent_id={$foreignDocument->id}"
    );

    $response->assertOk()->assertJsonCount(0, 'data');
});
