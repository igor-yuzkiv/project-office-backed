<?php

namespace App\Domains\Attachment\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class S3AttachmentStorageService implements AttachmentStorageService
{
    public function getProvider(): string
    {
        return 's3';
    }

    public function getDiskName(): string
    {
        return 'attachments';
    }

    public function store(UploadedFile $file, string $path): bool
    {
        return Storage::disk($this->getDiskName())->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->getDiskName())->exists($path);
    }

    public function delete(string $path): void
    {
        Storage::disk($this->getDiskName())->delete($path);
    }

    public function temporaryUrl(string $path): string
    {
        return Storage::disk($this->getDiskName())->temporaryUrl(
            $path,
            now()->addMinutes((int) config('filesystems.attachments_temporary_url_ttl_minutes'))
        );
    }

    public function streamResponse(string $path, ?string $originName = null): StreamedResponse
    {
        if (!$originName) {
            $originName = basename($path);
        }

        return Storage::disk($this->getDiskName())->response(
            $path,
            $originName
        );
    }
}
