<?php

use App\Domains\Annotation\Models\AnnotationModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function annotationAnchor(array $overrides = []): array
{
    return array_merge([
        'version'   => 1,
        'line'      => 12,
        'tag'       => 'p',
        'ordinal'   => 0,
        'index'     => 7,
        'text_hash' => '1a2b3c4d',
    ], $overrides);
}

function annotationPayload(array $overrides = []): array
{
    return array_merge([
        'content'       => 'This paragraph needs an example.',
        'text_snapshot' => 'The first sentence of the block.',
        'anchor'        => annotationAnchor(),
    ], $overrides);
}

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
    $this->document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
});

it('returns the annotations of the document with their authors, oldest first', function () {
    $author = UserModel::factory()->create(['name' => 'Wei Zhang']);

    $older = AnnotationModel::factory()->for($this->document, 'annotatable')->for($author, 'author')
        ->create(['content' => 'Older note', 'created_at' => now()->subHour()]);
    $newer = AnnotationModel::factory()->for($this->document, 'annotatable')->for($author, 'author')
        ->create(['content' => 'Newer note', 'created_at' => now()]);

    $response = $this->getJson("/api/project-documents/{$this->document->id}/annotations");

    $response->assertOk()
        ->assertJsonPath('data.0.id', $older->id)
        ->assertJsonPath('data.1.id', $newer->id)
        ->assertJsonPath('data.0.author.name', 'Wei Zhang');

    expect($response->json('data'))->toHaveCount(2);
});

it('does not return annotations of another document', function () {
    $otherDocument = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);

    AnnotationModel::factory()->for($otherDocument, 'annotatable')->for($this->user, 'author')->create();

    $response = $this->getJson("/api/project-documents/{$this->document->id}/annotations");

    $response->assertOk();

    expect($response->json('data'))->toBe([]);
});

it('returns an empty list for a document without annotations', function () {
    $response = $this->getJson("/api/project-documents/{$this->document->id}/annotations");

    $response->assertOk();

    expect($response->json('data'))->toBe([]);
});

it('creates an annotation and returns the anchor exactly as it was sent', function () {
    $response = $this->postJson("/api/project-documents/{$this->document->id}/annotations", annotationPayload());

    $response->assertCreated()
        ->assertJsonPath('data.content', 'This paragraph needs an example.')
        ->assertJsonPath('data.text_snapshot', 'The first sentence of the block.')
        ->assertJsonPath('data.anchor', annotationAnchor())
        ->assertJsonPath('data.author.id', $this->user->id);

    expect($this->document->annotations()->count())->toBe(1);
});

it('rejects an annotation that fails validation', function (array $payload) {
    $this->postJson("/api/project-documents/{$this->document->id}/annotations", $payload)
        ->assertStatus(422);
})->with([
    'without content'      => fn () => array_diff_key(annotationPayload(), ['content' => null]),
    'without anchor'       => fn () => array_diff_key(annotationPayload(), ['anchor' => null]),
    'unsupported version'  => fn () => annotationPayload(['anchor' => annotationAnchor(['version' => 2])]),
    'content beyond limit' => fn () => annotationPayload(['content' => str_repeat('x', 5001)]),
]);

it('updates the content of an own annotation', function () {
    $annotation = AnnotationModel::factory()->for($this->document, 'annotatable')->for($this->user, 'author')->create();

    $this->patchJson("/api/annotations/{$annotation->id}", annotationPayload(['content' => 'Rewritten note']))
        ->assertOk()
        ->assertJsonPath('data.content', 'Rewritten note');
});

it('stores the new anchor when an annotation is re-anchored', function () {
    $annotation = AnnotationModel::factory()->for($this->document, 'annotatable')->for($this->user, 'author')
        ->create(['content' => 'Unchanged note']);

    $newAnchor = annotationAnchor(['line' => 30, 'index' => 11, 'text_hash' => '00ff11aa']);

    $this->patchJson("/api/annotations/{$annotation->id}", annotationPayload([
        'content'       => 'Unchanged note',
        'anchor'        => $newAnchor,
        'text_snapshot' => 'The paragraph after the move.',
    ]))
        ->assertOk()
        ->assertJsonPath('data.anchor', $newAnchor)
        ->assertJsonPath('data.text_snapshot', 'The paragraph after the move.');
});

it('rejects an update without content, because the whole object is written', function () {
    $annotation = AnnotationModel::factory()->for($this->document, 'annotatable')->for($this->user, 'author')->create();

    $this->patchJson("/api/annotations/{$annotation->id}", array_diff_key(annotationPayload(), ['content' => null]))
        ->assertStatus(422);
});

it('deletes an own annotation', function () {
    $annotation = AnnotationModel::factory()->for($this->document, 'annotatable')->for($this->user, 'author')->create();

    $this->deleteJson("/api/annotations/{$annotation->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Annotation deleted.');

    expect(AnnotationModel::query()->count())->toBe(0);
});

it('rejects unauthenticated requests to every annotation route', function () {
    $annotation = AnnotationModel::factory()->for($this->document, 'annotatable')->for($this->user, 'author')->create();

    auth()->guard('web')->logout();
    app('auth')->forgetGuards();

    $this->getJson("/api/project-documents/{$this->document->id}/annotations")->assertUnauthorized();
    $this->postJson("/api/project-documents/{$this->document->id}/annotations", annotationPayload())->assertUnauthorized();
    $this->patchJson("/api/annotations/{$annotation->id}", annotationPayload())->assertUnauthorized();
    $this->deleteJson("/api/annotations/{$annotation->id}")->assertUnauthorized();
});
