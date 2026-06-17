<?php

use App\Domains\Attachment\Services\AttachmentStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('stores a file at the given path', function () {
    Storage::fake('attachments');

    $service = app(AttachmentStorageService::class);
    $file = UploadedFile::fake()->createWithContent('proposal.pdf', 'attachment content');
    $path = 'attachments/some-uuid.pdf';

    $result = $service->store($file, $path);

    expect($result)->toBeTrue();
    Storage::disk('attachments')->assertExists($path);
});

it('checks file existence by path', function () {
    Storage::fake('attachments');

    $service = app(AttachmentStorageService::class);
    $file = UploadedFile::fake()->createWithContent('notes.txt', 'notes');
    $path = 'attachments/some-uuid.txt';

    $service->store($file, $path);

    expect($service->exists($path))->toBeTrue();

    Storage::disk('attachments')->delete($path);

    expect($service->exists($path))->toBeFalse();
});

it('deletes a file by path', function () {
    Storage::fake('attachments');

    $service = app(AttachmentStorageService::class);
    $file = UploadedFile::fake()->createWithContent('report.docx', 'docx bytes');
    $path = 'attachments/some-uuid.docx';

    $service->store($file, $path);
    Storage::disk('attachments')->assertExists($path);

    $service->delete($path);

    Storage::disk('attachments')->assertMissing($path);
});

it('generates a temporary url for a path', function () {
    Storage::fake('attachments');

    $service = app(AttachmentStorageService::class);
    $file = UploadedFile::fake()->createWithContent('image.png', 'png bytes');
    $path = 'attachments/some-uuid.png';

    $service->store($file, $path);

    expect($service->temporaryUrl($path))
        ->toBeString()
        ->toContain($path);
});
