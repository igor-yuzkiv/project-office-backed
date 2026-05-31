<?php

namespace App\Domains\Attachment\Actions\UploadAttachment;

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Attachment\Services\AttachmentStorageService;

class UploadAttachmentHandler
{
    public function __construct(
        private readonly AttachmentStorageService $storageService,
    ) {}

    public function handle(UploadAttachmentCommand $command): AttachmentModel
    {
        return $this->storageService->store(
            file: $command->file,
            entityRef: $command->entityRef,
            role: $command->role,
        );
    }
}
