<?php

use App\Domains\Annotation\Actions\CreateAnnotation\CreateAnnotationCommand;
use App\Domains\Annotation\Actions\CreateAnnotation\CreateAnnotationHandler;
use App\Domains\Annotation\Actions\DeleteAnnotation\DeleteAnnotationCommand;
use App\Domains\Annotation\Actions\DeleteAnnotation\DeleteAnnotationHandler;
use App\Domains\Annotation\Actions\UpdateAnnotation\UpdateAnnotationCommand;
use App\Domains\Annotation\Actions\UpdateAnnotation\UpdateAnnotationHandler;
use App\Domains\Annotation\DTO\BlockAnchorDTO;
use App\Domains\Annotation\Models\AnnotationModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Actions\DeleteProjectDocument\DeleteProjectDocumentCommand;
use App\Domains\ProjectDocument\Actions\DeleteProjectDocument\DeleteProjectDocumentHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function anchorFixture(array $overrides = []): array
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

function documentFixture(): ProjectDocumentModel
{
    return ProjectDocumentModel::factory()->for(ProjectModel::factory(), 'project')->create();
}

it('attaches a created annotation to the document and stores its anchor', function () {
    $document = documentFixture();
    $author = UserModel::factory()->create();

    $annotation = app(CreateAnnotationHandler::class)->handle(new CreateAnnotationCommand(
        annotatable: $document,
        author: $author,
        content: 'Needs a clearer example here.',
        anchor: BlockAnchorDTO::fromArray(anchorFixture()),
        textSnapshot: 'The first sentence of the block.',
    ));

    expect(Str::isUlid($annotation->id))->toBeTrue()
        ->and($annotation->anchor)->toBe(anchorFixture())
        ->and($annotation->text_snapshot)->toBe('The first sentence of the block.')
        ->and($document->annotations()->pluck('id')->all())->toBe([$annotation->id]);
});

it('does not record an audit trail entry when an annotation is created', function () {
    $document = documentFixture();
    $author = UserModel::factory()->create();

    app(CreateAnnotationHandler::class)->handle(new CreateAnnotationCommand(
        annotatable: $document,
        author: $author,
        content: 'Review note.',
        anchor: BlockAnchorDTO::fromArray(anchorFixture()),
    ));

    expect(DB::table('audit_records')->count())->toBe(0);
});

it('updates content, anchor and text snapshot together', function () {
    $document = documentFixture();
    $author = UserModel::factory()->create();

    $annotation = AnnotationModel::factory()
        ->for($document, 'annotatable')
        ->for($author, 'author')
        ->create(['anchor' => anchorFixture()]);

    app(UpdateAnnotationHandler::class)->handle(new UpdateAnnotationCommand(
        annotation: $annotation,
        content: 'Re-anchored to the moved paragraph.',
        anchor: BlockAnchorDTO::fromArray(anchorFixture(['line' => 30, 'index' => 11, 'text_hash' => '00ff11aa'])),
        textSnapshot: 'The paragraph after the move.',
    ));

    // jsonb does not preserve key order, so the anchor is compared by content.
    expect($annotation->refresh()->content)->toBe('Re-anchored to the moved paragraph.')
        ->and($annotation->anchor)->toEqualCanonicalizing(anchorFixture(['line' => 30, 'index' => 11, 'text_hash' => '00ff11aa']))
        ->and($annotation->text_snapshot)->toBe('The paragraph after the move.');
});

it('deletes an annotation', function () {
    $annotation = AnnotationModel::factory()
        ->for(documentFixture(), 'annotatable')
        ->for(UserModel::factory(), 'author')
        ->create();

    app(DeleteAnnotationHandler::class)->handle(new DeleteAnnotationCommand($annotation));

    expect(AnnotationModel::query()->count())->toBe(0);
});

it('removes the annotations of a deleted document', function () {
    $document = documentFixture();

    AnnotationModel::factory()
        ->for($document, 'annotatable')
        ->for(UserModel::factory(), 'author')
        ->create();

    app(DeleteProjectDocumentHandler::class)->handle(new DeleteProjectDocumentCommand($document));

    expect(AnnotationModel::query()->count())->toBe(0);
});
