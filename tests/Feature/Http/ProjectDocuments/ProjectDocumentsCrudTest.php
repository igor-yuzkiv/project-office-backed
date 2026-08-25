<?php

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
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
        ->assertJsonPath('data.can_have_children', true)
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

it('creates a sub-document under a given parent', function () {
    $parent = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title'     => 'Child Document',
        'parent_id' => $parent->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.parent_id', $parent->id)
        ->assertJsonPath('data.depth', 1);
});

it('allows the same title under different parents', function () {
    $parentA = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $parentB = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'parent_id'  => $parentA->id,
        'title'      => 'Shared Child Title',
    ]);

    $response = $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title'     => 'Shared Child Title',
        'parent_id' => $parentB->id,
    ]);

    $response->assertCreated();
});

it('rejects creating a sub-document with a title that already exists under the same parent', function () {
    $parent = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'parent_id'  => $parent->id,
        'title'      => 'Duplicate Child',
    ]);

    $response = $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title'     => 'Duplicate Child',
        'parent_id' => $parent->id,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

it('rejects creating a document with a parent from another project', function () {
    $otherProject = ProjectModel::factory()->create();
    $foreignParent = ProjectDocumentModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title'     => 'Cross Project Child',
        'parent_id' => $foreignParent->id,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);
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
    $document = ProjectDocumentModel::factory()->withContent('Body that should not appear in the list.')->create([
        'project_id' => $this->project->id,
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

it('shows a single project document including its content and project', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Shown Document',
    ]);

    $response = $this->getJson('/api/project-documents/'.$document->id);

    $response->assertOk()
        ->assertJsonPath('data.id', $document->id)
        ->assertJsonPath('data.title', 'Shown Document')
        ->assertJsonPath('data.project.id', $this->project->id)
        ->assertJsonMissingPath('data.tasks');
});

it('includes linked tasks when explicitly requested via include', function () {
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $task = TaskModel::factory()->for($this->project, 'project')->create();
    $document->tasks()->attach($task);

    $response = $this->getJson('/api/project-documents/'.$document->id.'?include=tasks');

    $response->assertOk()->assertJsonPath('data.tasks.0.id', $task->id);
});

it('counts linked tasks and comments on a shown document', function () {
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $document->tasks()->attach(TaskModel::factory()->count(2)->for($this->project, 'project')->create());
    CommentModel::factory()->count(3)->create([
        'commentable_id'   => $document->id,
        'commentable_type' => $document->getMorphClass(),
        'author_id'        => $this->user->id,
    ]);

    $response = $this->getJson('/api/project-documents/'.$document->id);

    $response->assertOk()
        ->assertJsonPath('data.tasks_count', 2)
        ->assertJsonPath('data.comments_count', 3);
});

it('counts an unlinked document as zero rather than omitting the counts', function () {
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->getJson('/api/project-documents/'.$document->id);

    $response->assertOk()
        ->assertJsonPath('data.tasks_count', 0)
        ->assertJsonPath('data.comments_count', 0);
});

// The two counts feed two tab badges side by side, so a response carrying one and not the
// other leaves a badge silently blank.
it('carries both counts on every response that returns a single document', function () {
    $parent = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id, 'title' => 'Parent']);
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);

    $responses = [
        'store'  => $this->postJson("/api/projects/{$this->project->id}/project-documents", ['title' => 'Created']),
        'show'   => $this->getJson('/api/project-documents/'.$document->id),
        'update' => $this->putJson('/api/project-documents/'.$document->id, [
            'title'  => 'Renamed',
            'status' => $document->status->value,
        ]),
        'move' => $this->patchJson('/api/project-documents/'.$document->id.'/move', ['parent_id' => $parent->id]),
    ];

    foreach ($responses as $endpoint => $response) {
        $response->assertSuccessful();

        // None of these documents has a link, so a missing count and a wrong one both
        // read as something other than 0.
        expect($response->json('data.tasks_count'))->toBe(0, "{$endpoint} did not count tasks");
        expect($response->json('data.comments_count'))->toBe(0, "{$endpoint} did not count comments");
    }
});

it('does not include the ancestor path by default', function () {
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->getJson('/api/project-documents/'.$document->id);

    $response->assertOk()->assertJsonMissingPath('data.path');
});

it('includes the ancestor path from root to the document when requested via with_path', function () {
    $root = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id, 'title' => 'Root']);
    $child = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'parent_id'  => $root->id,
        'title'      => 'Child',
    ]);

    $response = $this->getJson('/api/project-documents/'.$child->id.'?with_path=1');

    $response->assertOk()
        ->assertJsonCount(2, 'data.path')
        ->assertJsonPath('data.path.0.id', $root->id)
        ->assertJsonPath('data.path.1.id', $child->id);
});

it('updates the title and tags of a project document', function () {
    $tag = TagModel::create(['name' => 'reviewed', 'color' => '#333333']);

    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Old Title',
    ]);

    $response = $this->putJson('/api/project-documents/'.$document->id, [
        'title'   => 'New Title',
        'status'  => $document->status->value,
        'tag_ids' => [$tag->id],
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'New Title');

    $document->refresh();
    expect($document->title)->toBe('New Title');
    expect($document->tags()->pluck('id')->all())->toBe([$tag->id]);
});

it('refuses content sent to the document update endpoint', function () {
    $document = ProjectDocumentModel::factory()->withContent('Original body.')->create([
        'project_id' => $this->project->id,
    ]);

    $this->putJson('/api/project-documents/'.$document->id, [
        'title'   => $document->title,
        'status'  => $document->status->value,
        'content' => 'Sent to the wrong endpoint.',
    ])->assertUnprocessable()->assertJsonValidationErrors(['content']);

    expect($document->effectiveVersion()->content)->toBe('Original body.');
});

it('updates the status of a project document', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'status'     => ProjectDocumentStatus::Draft->value,
    ]);

    $response = $this->putJson('/api/project-documents/'.$document->id, [
        'title'  => $document->title,
        'status' => ProjectDocumentStatus::Active->value,
    ]);

    $response->assertOk()->assertJsonPath('data.status', ProjectDocumentStatus::Active->value);

    $document->refresh();
    expect($document->status)->toBe(ProjectDocumentStatus::Active);
});

it('rejects an invalid status value on update', function () {
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->putJson('/api/project-documents/'.$document->id, [
        'title'  => $document->title,
        'status' => 'not-a-real-status',
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors(['status']);
});

it('deletes a single project document', function () {
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->deleteJson('/api/project-documents/'.$document->id);

    $response->assertOk()->assertJsonPath('message', 'Project document deleted.');
    expect(ProjectDocumentModel::find($document->id))->toBeNull();
});

it('deletes nested children and grandchildren when the root document is deleted', function () {
    $root = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $child = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id, 'parent_id' => $root->id]);
    $grandchild = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id, 'parent_id' => $child->id]);

    $this->deleteJson('/api/project-documents/'.$root->id)->assertOk();

    expect(ProjectDocumentModel::find($root->id))->toBeNull();
    expect(ProjectDocumentModel::find($child->id))->toBeNull();
    expect(ProjectDocumentModel::find($grandchild->id))->toBeNull();
});

it('leaves sibling documents intact when a document is deleted', function () {
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $sibling = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);

    $this->deleteJson('/api/project-documents/'.$document->id)->assertOk();

    expect(ProjectDocumentModel::find($sibling->id))->not->toBeNull();
});

it('cleans up task links, tag pivots and comments when a document is deleted', function () {
    $tag = TagModel::create(['name' => 'cleanup', 'color' => '#555555']);
    $task = TaskModel::factory()->for($this->project, 'project')->create();
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $document->tags()->attach($tag->id);
    $document->tasks()->attach($task->id);
    $comment = CommentModel::factory()->create([
        'commentable_id'   => $document->id,
        'commentable_type' => $document->getMorphClass(),
        'author_id'        => UserModel::factory()->create()->id,
    ]);

    $this->deleteJson('/api/project-documents/'.$document->id)->assertOk();

    expect(TaskModel::find($task->id))->not->toBeNull();
    expect(DB::table('project_document_task')->where('project_document_id', $document->id)->exists())->toBeFalse();
    expect(DB::table('taggables')->where('taggable_id', $document->id)->exists())->toBeFalse();
    expect(CommentModel::find($comment->id))->toBeNull();
    expect(TagModel::find($tag->id))->not->toBeNull();
});

it('deletes attachments through the storage-aware flow when a document is deleted', function () {
    Storage::fake('attachments');

    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    Storage::disk('attachments')->put('project-documents/'.$document->id.'/file.txt', 'content');
    $attachment = AttachmentModel::create([
        'original_name'    => 'file.txt',
        'storage_provider' => 's3',
        'storage_key'      => 'project-documents/'.$document->id.'/file.txt',
        'attachable_id'    => $document->id,
        'attachable_type'  => $document->getMorphClass(),
    ]);

    $this->deleteJson('/api/project-documents/'.$document->id)->assertOk();

    expect(AttachmentModel::find($attachment->id))->toBeNull();
    Storage::disk('attachments')->assertMissing('project-documents/'.$document->id.'/file.txt');
});

it('returns not found when deleting a non-existent document', function () {
    $response = $this->deleteJson('/api/project-documents/'.((string) Str::ulid()));

    $response->assertNotFound();
});

it('records a project_document.created event naming the project', function () {
    $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title' => 'Architecture',
    ])->assertCreated();

    $document = ProjectDocumentModel::query()->sole();
    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('project_document.created')
        ->and($record->title)->toBe("{$this->user->name} created «Architecture»")
        ->and($record->description)->toBe($this->project->name)
        ->and($record->subject_type)->toBe(ProjectDocumentModel::class)
        ->and($record->subject_id)->toBe($document->id);
});

it('records a project_document.updated event', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Architecture',
    ]);

    $this->putJson("/api/project-documents/{$document->id}", [
        'title'  => 'Architecture',
        'status' => ProjectDocumentStatus::InReview->value,
    ])->assertOk();

    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('project_document.updated')
        ->and($record->title)->toBe("{$this->user->name} updated «Architecture»")
        ->and($record->description)->toBeNull();
});

// Creating too deep is refused by the domain, moving too deep by a validator. The reader must
// not be able to tell which path they took.
it('gives the same depth message whether the document is created or moved too deep', function () {
    $branch = ProjectDocumentModel::factory()->for($this->project, 'project')->create();

    foreach (range(1, ProjectDocumentModel::maxDepth()) as $ignored) {
        $branch = ProjectDocumentModel::factory()
            ->for($this->project, 'project')
            ->create(['parent_id' => $branch->id]);
    }

    $created = $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title'     => 'One level too deep',
        'parent_id' => $branch->id,
    ])->assertStatus(422);

    $loose = ProjectDocumentModel::factory()->for($this->project, 'project')->create();

    $moved = $this->patchJson('/api/project-documents/'.$loose->id.'/move', [
        'parent_id' => $branch->id,
    ])->assertStatus(422);

    expect($moved->json('errors.parent_id.0'))->toBe($created->json('message'));
});

it('answers 422 when a document would be nested deeper than the limit allows', function () {
    $document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();

    foreach (range(1, ProjectDocumentModel::maxDepth()) as $ignored) {
        $document = ProjectDocumentModel::factory()
            ->for($this->project, 'project')
            ->create(['parent_id' => $document->id]);
    }

    $this->postJson("/api/projects/{$this->project->id}/project-documents", [
        'title'     => 'One level too deep',
        'parent_id' => $document->id,
    ])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Maximum document nesting depth (3 levels) exceeded.');
});
