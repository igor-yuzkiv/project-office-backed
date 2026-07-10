<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('updates the title and content', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Original',
        'content'    => 'Original body',
    ]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/docs/{$document->id}", [
        'title'   => 'Updated',
        'content' => 'Updated body',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated')
        ->assertJsonPath('data.content', 'Updated body');

    $fresh = $document->fresh();
    expect($fresh->title)->toBe('Updated');
    expect($fresh->content)->toBe('Updated body');
});

it('updates a document resolved by key', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id'      => $this->project->id,
        'key'             => 'DOC-MTM-3',
        'sequence_number' => 3,
    ]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/docs/DOC-MTM-3", [
        'title' => 'Renamed',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Renamed');
});

it('does not change the parent when a parent_id is sent', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Root',
    ]);
    $other = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Other',
    ]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/docs/{$document->id}", [
        'title'     => 'Root renamed',
        'parent_id' => $other->id,
    ]);

    $response->assertOk();
    expect($document->fresh()->parent_id)->toBeNull();
});

it('replaces tags from a comma-separated tags string', function () {
    $existing = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $document->tags()->attach([$existing->id]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/docs/{$document->id}", [
        'tags' => 'alpha,beta',
    ]);

    $response->assertOk();

    $names = $document->fresh()->tags()->pluck('name')->sort()->values()->all();
    expect($names)->toBe(['alpha', 'beta']);
});

it('clears tags when tags is an empty string', function () {
    $existing = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $document->tags()->attach([$existing->id]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/docs/{$document->id}", [
        'tags' => '',
    ]);

    $response->assertOk();

    expect($document->fresh()->tags()->count())->toBe(0);
});

it('does not change tags when tags is absent from the payload', function () {
    $existing = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $document->tags()->attach([$existing->id]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/docs/{$document->id}", [
        'status' => ProjectDocumentStatus::Active->value,
    ]);

    $response->assertOk();

    expect($document->fresh()->tags()->pluck('id')->all())->toBe([$existing->id]);
});

it('returns 404 when updating a document from another project', function () {
    $otherProject = ProjectModel::factory()->create();
    $document = ProjectDocumentModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/docs/{$document->id}", [
        'title' => 'Hijacked',
    ]);

    $response->assertNotFound();
    expect($document->fresh()->title)->not->toBe('Hijacked');
});
