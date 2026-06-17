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
        $attachment = $this->storageService->store(
            file: $command->file,
            role: $command->role,
        );

        if ($command->attachable !== null) {
            $attachment->attachable()->associate($command->attachable);
        }

        $attachment->save();

        return $attachment;
    }
}
