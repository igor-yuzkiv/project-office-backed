<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
    $this->document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
});

describe('listing versions', function () {
    it('returns the versions of a document in numbering order', function () {
        $this->document->versions()->create(['version_number' => 2, 'content' => 'Second']);
        $this->document->versions()->create(['version_number' => 1, 'content' => 'First', 'label' => 'Stable']);

        $this->getJson("/api/project-documents/{$this->document->id}/versions")
            ->assertOk()
            ->assertJsonPath('data.0.version_number', 1)
            ->assertJsonPath('data.0.label', 'Stable')
            ->assertJsonPath('data.0.content', 'First')
            ->assertJsonPath('data.1.version_number', 2);
    });

    it('marks the newest version as primary while nothing is pinned', function () {
        $this->document->versions()->create(['version_number' => 1]);
        $this->document->versions()->create(['version_number' => 2]);

        $this->getJson("/api/project-documents/{$this->document->id}/versions")
            ->assertOk()
            ->assertJsonPath('data.0.is_primary', false)
            ->assertJsonPath('data.1.is_primary', true);
    });

    it('marks the pinned version as primary even when a newer one exists', function () {
        $first = $this->document->versions()->create(['version_number' => 1]);
        $this->document->versions()->create(['version_number' => 2]);
        $this->document->update(['primary_version_id' => $first->id]);

        $this->getJson("/api/project-documents/{$this->document->id}/versions")
            ->assertOk()
            ->assertJsonPath('data.0.is_primary', true)
            ->assertJsonPath('data.1.is_primary', false);
    });

    it('returns an empty list for a document that has no versions', function () {
        $this->getJson("/api/project-documents/{$this->document->id}/versions")
            ->assertOk()
            ->assertJsonPath('data', []);
    });

    it('refuses an unauthenticated request', function () {
        auth()->logout();

        $this->getJson("/api/project-documents/{$this->document->id}/versions")->assertUnauthorized();
    });
});

describe('creating a version', function () {
    it('numbers a new version after the highest existing one', function () {
        $this->document->versions()->create(['version_number' => 4]);

        $this->postJson("/api/project-documents/{$this->document->id}/versions", [])
            ->assertCreated()
            ->assertJsonPath('data.version_number', 5)
            ->assertJsonPath('data.content', null);
    });

    it('gives consecutive numbers to two versions created in turn', function () {
        $first = $this->postJson("/api/project-documents/{$this->document->id}/versions", [])->assertCreated();
        $second = $this->postJson("/api/project-documents/{$this->document->id}/versions", [])->assertCreated();

        expect($second->json('data.version_number'))->toBe($first->json('data.version_number') + 1);
    });

    it('copies the content of the named version and nothing else', function () {
        $source = $this->document->versions()->create([
            'version_number' => 1,
            'content'        => 'Source body',
            'label'          => 'Source label',
        ]);

        $this->postJson("/api/project-documents/{$this->document->id}/versions", [
            'label'                        => 'Copy',
            'copy_content_from_version_id' => $source->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.content', 'Source body')
            ->assertJsonPath('data.label', 'Copy');
    });

    it('records the acting user as the author', function () {
        $response = $this->postJson("/api/project-documents/{$this->document->id}/versions", [])->assertCreated();

        expect($this->document->versions()->findOrFail($response->json('data.id'))->author_id)
            ->toBe($this->user->id);
    });

    it('refuses to copy from a version belonging to another document', function () {
        $other = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
        $foreign = $other->versions()->create(['version_number' => 1, 'content' => 'Not yours']);

        $this->postJson("/api/project-documents/{$this->document->id}/versions", [
            'copy_content_from_version_id' => $foreign->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['copy_content_from_version_id']);

        expect($this->document->versions()->count())->toBe(0);
    });
});

describe('saving one version', function () {
    it('saves the content of the version in the route', function () {
        $version = $this->document->versions()->create(['version_number' => 1, 'content' => 'First', 'label' => 'Stable']);
        $other = $this->document->versions()->create(['version_number' => 2, 'content' => 'Second']);

        $this->putJson("/api/project-documents/{$this->document->id}/versions/{$version->id}", [
            'content' => 'First edited',
        ])->assertOk()
            ->assertJsonPath('data.id', $version->id)
            ->assertJsonPath('data.content', 'First edited')
            ->assertJsonPath('data.label', 'Stable')
            ->assertJsonPath('data.is_primary', false);

        expect($version->fresh()->content)->toBe('First edited')
            ->and($other->fresh()->content)->toBe('Second');
    });

    it('accepts null content', function () {
        $version = $this->document->versions()->create(['version_number' => 1, 'content' => 'Body']);

        $this->putJson("/api/project-documents/{$this->document->id}/versions/{$version->id}", [
            'content' => null,
        ])->assertOk();

        expect($version->fresh()->content)->toBeNull();
    });

    it('requires the content key to be sent', function () {
        $version = $this->document->versions()->create(['version_number' => 1, 'content' => 'Body']);

        $this->putJson("/api/project-documents/{$this->document->id}/versions/{$version->id}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    });

    it('does not find a version through another document', function () {
        $other = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
        $foreign = $other->versions()->create(['version_number' => 1, 'content' => 'Theirs']);

        $this->putJson("/api/project-documents/{$this->document->id}/versions/{$foreign->id}", [
            'content' => 'Hijacked',
        ])->assertNotFound();

        expect($foreign->fresh()->content)->toBe('Theirs');
    });

    it('answers 404 for a version that does not exist', function () {
        $this->putJson("/api/project-documents/{$this->document->id}/versions/".((string) Str::ulid()), [
            'content' => 'Anything',
        ])->assertNotFound();
    });

    it('refuses an unauthenticated request', function () {
        $version = $this->document->versions()->create(['version_number' => 1, 'content' => 'Body']);
        auth()->logout();

        $this->putJson("/api/project-documents/{$this->document->id}/versions/{$version->id}", [
            'content' => 'Anything',
        ])->assertUnauthorized();

        expect($version->fresh()->content)->toBe('Body');
    });

    it('records one version event and touches the document', function () {
        $version = $this->document->versions()->create(['version_number' => 3, 'content' => 'Body']);
        $this->travel(1)->minute();

        $this->putJson("/api/project-documents/{$this->document->id}/versions/{$version->id}", [
            'content' => 'Rewritten',
        ])->assertOk();

        $record = AuditRecordModel::query()->sole();
        expect($record->type)->toBe('project_document_version.updated')
            ->and($record->title)->toBe("{$this->user->name} updated version 3 of «{$this->document->title}»")
            ->and($record->description)->toBe('Changed content')
            ->and($this->document->fresh()->updated_at)->not->toEqual($this->document->updated_at);
    });

    it('records nothing when the content comes back unchanged', function () {
        $version = $this->document->versions()->create(['version_number' => 1, 'content' => 'Body']);

        $this->putJson("/api/project-documents/{$this->document->id}/versions/{$version->id}", [
            'content' => 'Body',
        ])->assertOk();

        expect(AuditRecordModel::query()->count())->toBe(0);
    });
});

describe('naming a version', function () {
    it('saves the name and leaves the content alone', function () {
        $version = $this->document->versions()->create(['version_number' => 1, 'content' => 'Body']);

        $this->putJson("/api/project-document-versions/{$version->id}", ['label' => 'Stable'])
            ->assertOk()
            ->assertJsonPath('data.label', 'Stable')
            ->assertJsonPath('data.content', 'Body')
            ->assertJsonPath('data.is_primary', true);

        expect($version->fresh()->label)->toBe('Stable');
    });

    it('clears the name with null', function () {
        $version = $this->document->versions()->create(['version_number' => 1, 'label' => 'Stable']);

        $this->putJson("/api/project-document-versions/{$version->id}", ['label' => null])->assertOk();

        expect($version->fresh()->label)->toBeNull();
    });

    it('requires the label key and bounds its length', function () {
        $version = $this->document->versions()->create(['version_number' => 1]);

        $this->putJson("/api/project-document-versions/{$version->id}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['label']);
        $this->putJson("/api/project-document-versions/{$version->id}", ['label' => str_repeat('a', 256)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['label']);
    });

    it('answers 404 for a version that does not exist', function () {
        $this->putJson('/api/project-document-versions/'.((string) Str::ulid()), ['label' => 'x'])->assertNotFound();
    });

    it('records one version event and touches the document', function () {
        $version = $this->document->versions()->create(['version_number' => 2, 'label' => 'Old']);
        $this->travel(1)->minute();

        $this->putJson("/api/project-document-versions/{$version->id}", ['label' => 'New'])->assertOk();

        $record = AuditRecordModel::query()->sole();
        expect($record->title)->toBe("{$this->user->name} updated version 2 of «{$this->document->title}»")
            ->and($record->description)->toBe('Changed label')
            ->and($this->document->fresh()->updated_at)->not->toEqual($this->document->updated_at);
    });

    it('records nothing when the name comes back unchanged', function () {
        $version = $this->document->versions()->create(['version_number' => 1, 'label' => 'Same']);

        $this->putJson("/api/project-document-versions/{$version->id}", ['label' => 'Same'])->assertOk();

        expect(AuditRecordModel::query()->count())->toBe(0);
    });
});

describe('deleting a version', function () {
    it('deletes a version permanently', function () {
        $version = $this->document->versions()->create(['version_number' => 1]);

        $this->deleteJson("/api/project-document-versions/{$version->id}")->assertNoContent();

        expect($this->document->versions()->count())->toBe(0);
    });

    it('unpins the primary version when that version is deleted', function () {
        $first = $this->document->versions()->create(['version_number' => 1, 'content' => 'First']);
        $second = $this->document->versions()->create(['version_number' => 2, 'content' => 'Second']);
        $this->document->update(['primary_version_id' => $first->id]);

        $this->deleteJson("/api/project-document-versions/{$first->id}")->assertNoContent();

        $document = $this->document->fresh();
        expect($document->primary_version_id)->toBeNull()
            ->and($document->effectiveVersion()->id)->toBe($second->id);
    });

    it('allows deleting the only version, leaving the document without any', function () {
        $version = $this->document->versions()->create(['version_number' => 1, 'content' => 'Only']);

        $this->deleteJson("/api/project-document-versions/{$version->id}")->assertNoContent();

        $this->getJson("/api/project-documents/{$this->document->id}")
            ->assertOk()
            ->assertJsonPath('data.content', null)
            ->assertJsonPath('data.version', null);
    });
});

describe('choosing the primary version', function () {
    it('pins a version as primary', function () {
        $first = $this->document->versions()->create(['version_number' => 1, 'content' => 'First']);
        $this->document->versions()->create(['version_number' => 2, 'content' => 'Second']);

        $this->putJson("/api/project-documents/{$this->document->id}/primary-version", [
            'version_id' => $first->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.primary_version_id', $first->id)
            ->assertJsonPath('data.content', 'First');
    });

    it('returns to following the newest version when the pin is cleared', function () {
        $first = $this->document->versions()->create(['version_number' => 1, 'content' => 'First']);
        $this->document->versions()->create(['version_number' => 2, 'content' => 'Second']);
        $this->document->update(['primary_version_id' => $first->id]);

        $this->putJson("/api/project-documents/{$this->document->id}/primary-version", [
            'version_id' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.primary_version_id', null)
            ->assertJsonPath('data.content', 'Second');
    });

    it('refuses to pin a version belonging to another document', function () {
        $other = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
        $foreign = $other->versions()->create(['version_number' => 1]);

        $this->putJson("/api/project-documents/{$this->document->id}/primary-version", [
            'version_id' => $foreign->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['version_id']);

        expect($this->document->fresh()->primary_version_id)->toBeNull();
    });

    it('requires the version_id key to be sent', function () {
        $this->putJson("/api/project-documents/{$this->document->id}/primary-version", [])
            ->assertUnprocessable()->assertJsonValidationErrors(['version_id']);
    });
});

describe('what reaches the activity feed', function () {
    it('reports creating a version and nothing about the document', function () {
        $this->postJson("/api/project-documents/{$this->document->id}/versions", ['label' => 'Draft two'])
            ->assertCreated();

        $record = AuditRecordModel::query()->sole();
        expect($record->type)->toBe('project_document_version.created')
            ->and($record->title)->toBe("{$this->user->name} created version 1 of «{$this->document->title}»")
            ->and($record->description)->toBe('Draft two')
            ->and($record->subject_id)->toBe($this->document->id);
    });

    it('reports deleting a version with the number that disappeared', function () {
        $version = $this->document->versions()->create(['version_number' => 3, 'label' => 'Old']);

        $this->deleteJson("/api/project-document-versions/{$version->id}")->assertNoContent();

        $record = AuditRecordModel::query()->sole();
        expect($record->type)->toBe('project_document_version.deleted')
            ->and($record->title)->toBe("{$this->user->name} deleted version 3 of «{$this->document->title}»")
            ->and($record->description)->toBe('Old')
            ->and($record->subject_id)->toBe($this->document->id);
    });

    it('reports pinning a version as primary', function () {
        $version = $this->document->versions()->create(['version_number' => 2]);

        $this->putJson("/api/project-documents/{$this->document->id}/primary-version", [
            'version_id' => $version->id,
        ])->assertOk();

        $record = AuditRecordModel::query()->sole();
        expect($record->type)->toBe('project_document_version.primary_changed')
            ->and($record->title)->toBe("{$this->user->name} made version 2 primary for «{$this->document->title}»");
    });

    it('reports going back to following the latest version', function () {
        $version = $this->document->versions()->create(['version_number' => 1]);
        $this->document->update(['primary_version_id' => $version->id]);

        $this->putJson("/api/project-documents/{$this->document->id}/primary-version", ['version_id' => null])
            ->assertOk();

        expect(AuditRecordModel::query()->sole()->title)
            ->toBe("{$this->user->name} made «{$this->document->title}» follow its latest version");
    });

    it('reports nothing when the primary version is set to what it already was', function () {
        $version = $this->document->versions()->create(['version_number' => 1]);
        $this->document->update(['primary_version_id' => $version->id]);

        $this->putJson("/api/project-documents/{$this->document->id}/primary-version", [
            'version_id' => $version->id,
        ])->assertOk();

        expect(AuditRecordModel::query()->count())->toBe(0);
    });

    it('still reports a document update on its own fields alone', function () {
        $this->document->versions()->create(['version_number' => 1, 'content' => 'Body']);

        $this->putJson("/api/project-documents/{$this->document->id}", [
            'title'  => 'Renamed',
            'status' => $this->document->status->value,
        ])->assertOk();

        expect(AuditRecordModel::query()->pluck('type')->all())->toBe(['project_document.updated']);
    });
});
