<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Actions\Version\WriteProjectDocumentContent\WriteProjectDocumentContentCommand;
use App\Domains\ProjectDocument\Actions\Version\WriteProjectDocumentContent\WriteProjectDocumentContentHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Database\Backfills\ProjectDocumentContentBackfill;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->project = ProjectModel::factory()->create();
});

it('resolves the newest version when no primary version is pinned', function () {
    $document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
    $document->versions()->create(['version_number' => 1, 'content' => 'First']);
    $document->versions()->create(['version_number' => 2, 'content' => 'Second']);

    expect($document->effectiveVersion()->content)->toBe('Second');
});

it('resolves the pinned version even when a newer one exists', function () {
    $document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
    $first = $document->versions()->create(['version_number' => 1, 'content' => 'First']);
    $document->versions()->create(['version_number' => 2, 'content' => 'Second']);

    $document->update(['primary_version_id' => $first->id]);

    expect($document->fresh()->effectiveVersion()->content)->toBe('First');
});

it('resolves no version for a document that has none', function () {
    $document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();

    expect($document->effectiveVersion())->toBeNull();
});

it('refuses two versions with the same number on one document', function () {
    $document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
    $document->versions()->create(['version_number' => 1]);

    expect(fn () => $document->versions()->create(['version_number' => 1]))
        ->toThrow(QueryException::class);
});

it('allows the same version number on different documents', function () {
    $first = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
    $second = ProjectDocumentModel::factory()->for($this->project, 'project')->create();

    $first->versions()->create(['version_number' => 1]);
    $second->versions()->create(['version_number' => 1]);

    expect($second->versions()->count())->toBe(1);
});

it('deletes the versions of a deleted document', function () {
    $document = ProjectDocumentModel::factory()->withContent('Body')->create(['project_id' => $this->project->id]);

    $document->delete();

    expect(DB::table('project_document_versions')->where('project_document_id', $document->id)->count())->toBe(0);
});

describe('writing content without knowing about versions', function () {
    it('creates a first version when the document has none', function () {
        $document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();

        (new WriteProjectDocumentContentHandler)->handle(
            new WriteProjectDocumentContentCommand($document, 'Written'),
        );

        expect($document->versions()->count())->toBe(1)
            ->and($document->effectiveVersion()->version_number)->toBe(1)
            ->and($document->effectiveVersion()->content)->toBe('Written');
    });

    it('overwrites the effective version in place instead of adding one', function () {
        $document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
        $document->versions()->create(['version_number' => 1, 'content' => 'First']);
        $second = $document->versions()->create(['version_number' => 2, 'content' => 'Second']);

        (new WriteProjectDocumentContentHandler)->handle(
            new WriteProjectDocumentContentCommand($document, 'Rewritten'),
        );

        expect($document->versions()->count())->toBe(2)
            ->and($second->fresh()->content)->toBe('Rewritten');
    });

    it('writes into the pinned version rather than the newest one', function () {
        $document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
        $first = $document->versions()->create(['version_number' => 1, 'content' => 'First']);
        $second = $document->versions()->create(['version_number' => 2, 'content' => 'Second']);
        $document->update(['primary_version_id' => $first->id]);

        (new WriteProjectDocumentContentHandler)->handle(
            new WriteProjectDocumentContentCommand($document->fresh(), 'Rewritten'),
        );

        expect($first->fresh()->content)->toBe('Rewritten')
            ->and($second->fresh()->content)->toBe('Second');
    });
});

describe('the content backfill', function () {
    // The backfill runs against the schema as it was before the migration dropped the column,
    // so the test puts that column back for the duration of the check.
    beforeEach(function () {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->longText('content')->nullable();
        });
    });

    afterEach(function () {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    });

    it('moves the content of a document into its first version', function () {
        $document = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
        DB::table('project_documents')->where('id', $document->id)->update(['content' => 'Legacy body']);

        (new ProjectDocumentContentBackfill)->run();

        $version = $document->effectiveVersion();
        expect($version->version_number)->toBe(1)
            ->and($version->content)->toBe('Legacy body')
            ->and($version->author_id)->toBeNull()
            ->and($document->fresh()->primary_version_id)->toBeNull();
    });

    it('leaves a document without content without any version', function () {
        $empty = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
        $blank = ProjectDocumentModel::factory()->for($this->project, 'project')->create();
        DB::table('project_documents')->where('id', $blank->id)->update(['content' => '']);

        (new ProjectDocumentContentBackfill)->run();

        expect($empty->versions()->count())->toBe(0)
            ->and($blank->versions()->count())->toBe(0);
    });
});
