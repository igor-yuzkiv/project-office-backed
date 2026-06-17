<?php

namespace App\Domains\Attachment\Services;

use App\Domains\Attachment\Models\AttachmentModel;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface AttachmentStorageService
{
    public function store(
        UploadedFile $file,
        ?string $role = null
    ): AttachmentModel;

    public function temporaryUrl(AttachmentModel $attachment): string;

    public function streamResponse(AttachmentModel $attachment): StreamedResponse;

    public function exists(AttachmentModel $attachment): bool;

    public function delete(AttachmentModel $attachment): void;
}
