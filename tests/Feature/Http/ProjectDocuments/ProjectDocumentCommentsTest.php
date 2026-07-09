<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
    $this->document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
});

it('returns paginated comments for the document', function () {
    $this->document->comments()->createMany(
        collect(range(1, 3))->map(fn (int $i) => [
            'author_id' => UserModel::factory()->create()->id,
            'content'   => "Comment {$i}",
        ])->all()
    );

    $response = $this->getJson("/api/project-documents/{$this->document->id}/comments");

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);

    expect($response->json('meta.total'))->toBe(3);
});

it('does not return comments from other documents', function () {
    $otherDocument = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $otherDocument->comments()->create([
        'author_id' => UserModel::factory()->create()->id,
        'content'   => 'Other document comment',
    ]);

    $response = $this->getJson("/api/project-documents/{$this->document->id}/comments");

    $response->assertOk();

    expect($response->json('meta.total'))->toBe(0);
});

it('creates a comment on the document', function () {
    $response = $this->postJson("/api/project-documents/{$this->document->id}/comments", [
        'content' => 'A note on this document',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.content', 'A note on this document');

    expect($this->document->comments()->count())->toBe(1);
});
