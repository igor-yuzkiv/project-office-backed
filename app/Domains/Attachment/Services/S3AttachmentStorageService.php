<?php

namespace App\Domains\Attachment\Services;

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Shared\ValueObjects\EntityRef;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class S3AttachmentStorageService implements AttachmentStorageService
{
    private const string STORAGE_PROVIDER = 's3';

    public function store(
        UploadedFile $file,
        ?EntityRef $entityRef = null,
        ?string $role = null
    ): AttachmentModel {
        $attachment = new AttachmentModel([
            'original_name'    => $file->getClientOriginalName(),
            'extension'        => $file->getClientOriginalExtension() ?: null,
            'mime_type'        => $file->getClientMimeType(),
            'size_bytes'       => $file->getSize(),
            'storage_provider' => self::STORAGE_PROVIDER,
            'entity_type'      => $entityRef?->module,
            'entity_id'        => $entityRef?->id,
            'role'             => $role,
        ]);

        $attachment->storage_key = $this->storageKey($attachment, $file);

        $stored = Storage::disk('attachments')->putFileAs(
            dirname($attachment->storage_key),
            $file,
            basename($attachment->storage_key)
        );

        if ($stored === false) {
            throw new RuntimeException('Attachment file could not be stored.');
        }

        $attachment->save();

        return $attachment;
    }

    public function temporaryUrl(AttachmentModel $attachment): string
    {
        return Storage::disk('attachments')->temporaryUrl(
            $attachment->storage_key,
            now()->addMinutes((int) config('filesystems.attachments_temporary_url_ttl_minutes'))
        );
    }

    public function exists(AttachmentModel $attachment): bool
    {
        return Storage::disk('attachments')->exists($attachment->storage_key);
    }

    public function delete(AttachmentModel $attachment): void
    {
        Storage::disk('attachments')->delete($attachment->storage_key);
    }

    private function storageKey(AttachmentModel $attachment, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        if ($extension === '') {
            return "attachments/{$attachment->id}";
        }

        return "attachments/{$attachment->id}.{$extension}";
    }
}
