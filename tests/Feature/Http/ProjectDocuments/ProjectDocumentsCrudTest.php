<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
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

it('creates a project document with default draft status and top-level hierarchy', function () {
    $response = $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title' => 'Architecture Notes',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Architecture Notes')
        ->assertJsonPath('data.project_id', $this->project->id)
        ->assertJsonPath('data.parent_id', null)
        ->assertJsonPath('data.status', ProjectDocumentStatus::Draft->value)
        ->assertJsonPath('data.depth', 0)
        ->assertJsonPath('data.project.id', $this->project->id);

    $document = ProjectDocumentModel::findOrFail($response->json('data.id'));
    expect($document->path)->toBe($document->id);
    expect($document->depth)->toBe(0);
});

it('creates a project document with tags', function () {
    $tagA = TagModel::create(['name' => 'spec', 'color' => '#111111']);
    $tagB = TagModel::create(['name' => 'draft', 'color' => '#222222']);

    $response = $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title'   => 'Tagged Document',
        'tag_ids' => [$tagA->id, $tagB->id],
    ]);

    $response->assertCreated();

    $document = ProjectDocumentModel::findOrFail($response->json('data.id'));
    expect($document->tags()->pluck('id')->sort()->values()->all())
        ->toBe(collect([$tagA->id, $tagB->id])->sort()->values()->all());
});

it('rejects creating a document with a title that already exists at the root of the project', function () {
    ProjectDocumentModel::factory()->create(['project_id' => $this->project->id, 'title' => 'Duplicate']);

    $response = $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title' => 'Duplicate',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

it('allows the same title in different projects', function () {
    $otherProject = ProjectModel::factory()->create();
    ProjectDocumentModel::factory()->create(['project_id' => $otherProject->id, 'title' => 'Shared Title']);

    $response = $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title' => 'Shared Title',
    ]);

    $response->assertCreated();
});

it('rejects creating a document for a non-existent project', function () {
    $response = $this->postJson('/api/projects/'.((string) Str::ulid()).'/project-documents', [
        'title' => 'Orphan Document',
    ]);

    $response->assertNotFound();
});

it('lists only documents belonging to the given project', function () {
    $otherProject = ProjectModel::factory()->create();

    $mine = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    ProjectDocumentModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->getJson("/api/projects/{$this->project->id}/project-documents");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $mine->id);
});

it('includes tags in the project document list, but not content', function () {
    $tag = TagModel::create(['name' => 'listed', 'color' => '#444444']);
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'content'    => 'Body that should not appear in the list.',
    ]);
    $document->tags()->sync([$tag->id]);

    $response = $this->getJson("/api/projects/{$this->project->id}/project-documents");

    $response->assertOk()
        ->assertJsonPath('data.0.tags.0.id', $tag->id)
        ->assertJsonMissingPath('data.0.content');
});

it('rejects listing documents for a non-existent project', function () {
    $response = $this->getJson('/api/projects/'.((string) Str::ulid()).'/project-documents');

    $response->assertNotFound();
});

it('includes project and tasks in the list when explicitly requested via include', function () {
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $task = TaskModel::factory()->for($this->project, 'project')->create();
    $document->tasks()->attach($task);

    $response = $this->getJson("/api/projects/{$this->project->id}/project-documents?include=project,tasks");

    $response->assertOk()
        ->assertJsonPath('data.0.project.id', $this->project->id)
        ->assertJsonPath('data.0.tasks.0.id', $task->id)
        ->assertJsonMissingPath('data.0.content');
});

it('rejects an unknown include on the project document list', function () {
    $response = $this->getJson("/api/projects/{$this->project->id}/project-documents?include=bogus");

    $response->assertUnprocessable();
});

it('shows a single project document including its content, project and linked tasks', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Shown Document',
    ]);
    $task = TaskModel::factory()->for($this->project, 'project')->create();
    $document->tasks()->attach($task);

    $response = $this->getJson('/api/project-documents/'.$document->id);

    $response->assertOk()
        ->assertJsonPath('data.id', $document->id)
        ->assertJsonPath('data.title', 'Shown Document')
        ->assertJsonPath('data.project.id', $this->project->id)
        ->assertJsonPath('data.tasks.0.id', $task->id);
});

it('updates the title, content and tags of a project document', function () {
    $tag = TagModel::create(['name' => 'reviewed', 'color' => '#333333']);

    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Old Title',
        'content'    => null,
    ]);

    $response = $this->putJson('/api/project-documents/'.$document->id, [
        'title'   => 'New Title',
        'content' => 'Updated body.',
        'tag_ids' => [$tag->id],
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'New Title')
        ->assertJsonPath('data.content', 'Updated body.');

    $document->refresh();
    expect($document->title)->toBe('New Title');
    expect($document->content)->toBe('Updated body.');
    expect($document->tags()->pluck('id')->all())->toBe([$tag->id]);
});

it('clears the content when explicitly updated with null', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'content'    => 'Some body.',
    ]);

    $response = $this->putJson('/api/project-documents/'.$document->id, [
        'content' => null,
    ]);

    $response->assertOk()->assertJsonPath('data.content', null);

    $document->refresh();
    expect($document->content)->toBeNull();
});

it('leaves the title untouched when only content is updated', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Untouched Title',
    ]);

    $this->putJson('/api/project-documents/'.$document->id, ['content' => 'New body.'])
        ->assertOk()
        ->assertJsonPath('data.title', 'Untouched Title');
});
