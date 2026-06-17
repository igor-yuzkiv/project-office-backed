<?php

namespace App\Domains\Attachment\Services;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface AttachmentStorageService
{
    public function getProvider(): string;

    public function getDiskName(): string;

    public function store(UploadedFile $file, string $path): bool;

    public function exists(string $path): bool;

    public function delete(string $path): void;

    public function temporaryUrl(string $path): string;

    public function streamResponse(string $path, ?string $originName = null): StreamedResponse;
}
