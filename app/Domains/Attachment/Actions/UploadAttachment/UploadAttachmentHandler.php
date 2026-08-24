<?php

namespace App\Domains\Attachment\Actions\UploadAttachment;

use App\Domains\Attachment\AuditRecords\AttachmentUploadedAuditRecord;
use App\Domains\Attachment\Exceptions\AttachmentStorageFailedException;
use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Attachment\Services\AttachmentStorageService;
use App\Domains\Attachment\ValueObjects\AttachmentStorageKey;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

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
            throw AttachmentStorageFailedException::couldNotStoreFile();
        }

        if ($command->attachable !== null) {
            $attachment->attachable()->associate($command->attachable);
        }

        $attachment->save();

        // An avatar is attached to the user themselves, and a feed full of avatar changes is
        // noise; a file with no carrier has nowhere to link to.
        if ($command->attachable !== null && !$command->attachable instanceof UserModel) {
            AuditTrail::capture(new AttachmentUploadedAuditRecord($attachment, $command->attachable));
        }

        return $attachment;
    }
}
