<?php

namespace App\Domains\Attachment\Actions\UploadAttachment;

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Attachment\Services\AttachmentStorageService;
use App\Domains\Attachment\ValueObjects\AttachmentStorageKey;

class UploadAttachmentHandler
{
    public function __construct(
        private readonly AttachmentStorageService $storageService,
    ) {}

    public function handle(UploadAttachmentCommand $command): AttachmentModel
    {
        $attachment = new AttachmentModel([
            'original_name'    => $command->file->getClientOriginalName(),
            'extension'        => $command->file->getClientOriginalExtension(),
            'mime_type'        => $command->file->getClientMimeType(),
            'size_bytes'       => $command->file->getSize(),
            'storage_provider' => $this->storageService->getProvider(),
            'role'             => $command->role,
        ]);

        $attachment->setUniqueIds();
        $attachment->storage_key = AttachmentStorageKey::make($attachment->id, $command->file->getClientOriginalExtension());

        $stored = $this->storageService->store($command->file, $attachment->storage_key);
        if ($stored === false) {
            throw new \RuntimeException('Attachment file could not be stored.');
        }

        if ($command->attachable !== null) {
            $attachment->attachable()->associate($command->attachable);
        }

        $attachment->save();

        return $attachment;
    }
}
