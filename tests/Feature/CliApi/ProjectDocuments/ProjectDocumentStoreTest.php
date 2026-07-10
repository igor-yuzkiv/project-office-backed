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

it('creates a root document using the project from the route', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/docs", [
        'title'   => 'New Document',
        'content' => 'Body',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'New Document')
        ->assertJsonPath('data.content', 'Body');

    $document = ProjectDocumentModel::findOrFail($response->json('data.id'));
    expect($document->project_id)->toBe($this->project->id);
    expect($document->parent_id)->toBeNull();
});

it('returns the created document with a project-prefixed key and root path', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/docs", [
        'title' => 'New Document',
    ]);

    $id = $response->json('data.id');
    $response->assertCreated()
        ->assertJsonPath('data.key', 'DOC-'.$this->project->prefix.'-1')
        ->assertJsonPath('data.path.0.id', $id)
        ->assertJsonPath('data.path', fn (array $path) => count($path) === 1);
});

it('requires a title', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/docs", [
        'content' => 'Body',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

it('rejects a duplicate root title within the same project', function () {
    ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Existing',
    ]);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/docs", [
        'title' => 'Existing',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

it('creates new tags from a comma-separated tags string', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/docs", [
        'title' => 'New Document',
        'tags'  => 'bug, Backend , urgent',
    ]);

    $response->assertCreated();

    $document = ProjectDocumentModel::findOrFail($response->json('data.id'));
    expect($document->tags()->pluck('name')->sort()->values()->all())
        ->toBe(['backend', 'bug', 'urgent']);
});

it('reuses an existing tag matched by normalized name', function () {
    $existing = TagModel::create(['name' => 'backend', 'color' => '#111111']);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/docs", [
        'title' => 'New Document',
        'tags'  => 'Backend,frontend',
    ]);

    $response->assertCreated();

    $document = ProjectDocumentModel::findOrFail($response->json('data.id'));
    expect($document->tags()->pluck('id')->contains($existing->id))->toBeTrue();
    expect(TagModel::where('name', 'backend')->count())->toBe(1);
});

it('ignores an empty tags string', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/docs", [
        'title' => 'New Document',
        'tags'  => '',
    ]);

    $response->assertCreated();

    $document = ProjectDocumentModel::findOrFail($response->json('data.id'));
    expect($document->tags()->count())->toBe(0);
});

it('rejects a tag name longer than 64 characters', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/docs", [
        'title' => 'New Document',
        'tags'  => str_repeat('a', 65),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['tags']);
});
