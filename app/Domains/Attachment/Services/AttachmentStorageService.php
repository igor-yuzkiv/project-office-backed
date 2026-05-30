<?php

namespace App\Domains\Attachment\Services;

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Shared\ValueObjects\EntityRef;
use Illuminate\Http\UploadedFile;

interface AttachmentStorageService
{
    public function store(
        UploadedFile $file,
        ?EntityRef $entityRef = null,
        ?string $role = null
    ): AttachmentModel;

    public function temporaryUrl(AttachmentModel $attachment): string;

    public function exists(AttachmentModel $attachment): bool;

    public function delete(AttachmentModel $attachment): void;
}
