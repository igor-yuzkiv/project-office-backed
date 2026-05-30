<?php

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Attachment\Services\AttachmentStorageService;
use App\Domains\Shared\ValueObjects\EntityRef;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('stores a file and creates an attachment record', function () {
    Storage::fake('attachments');

    $service = app(AttachmentStorageService::class);
    $file = UploadedFile::fake()->createWithContent('proposal.pdf', 'attachment content');

    $attachment = $service->store($file, new EntityRef('task-1', 'task'), 'document');

    expect($attachment)->toBeInstanceOf(AttachmentModel::class)
        ->and($attachment->exists)->toBeTrue()
        ->and($attachment->original_name)->toBe('proposal.pdf')
        ->and($attachment->extension)->toBe('pdf')
        ->and($attachment->mime_type)->toBe('application/pdf')
        ->and($attachment->size_bytes)->toBe(strlen('attachment content'))
        ->and($attachment->storage_provider)->toBe('s3')
        ->and($attachment->storage_key)->toStartWith('attachments/')
        ->and($attachment->storage_key)->toEndWith('.pdf')
        ->and($attachment->storage_key)->not->toStartWith('http')
        ->and($attachment->entity_type)->toBe('task')
        ->and($attachment->entity_id)->toBe('task-1')
        ->and($attachment->role)->toBe('document');

    Storage::disk('attachments')->assertExists($attachment->storage_key);
    expect(AttachmentModel::query()->whereKey($attachment->id)->exists())->toBeTrue();
});

it('generates a temporary url for an attachment', function () {
    Storage::fake('attachments');

    $service = app(AttachmentStorageService::class);
    $attachment = $service->store(
        UploadedFile::fake()->createWithContent('image.png', 'png bytes')
    );

    expect($service->temporaryUrl($attachment))
        ->toBeString()
        ->toContain($attachment->storage_key);
});

it('checks attachment file existence', function () {
    Storage::fake('attachments');

    $service = app(AttachmentStorageService::class);
    $attachment = $service->store(
        UploadedFile::fake()->createWithContent('notes.txt', 'notes')
    );

    expect($service->exists($attachment))->toBeTrue();

    Storage::disk('attachments')->delete($attachment->storage_key);

    expect($service->exists($attachment))->toBeFalse();
});
