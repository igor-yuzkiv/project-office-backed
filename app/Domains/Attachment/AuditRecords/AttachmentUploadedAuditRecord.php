<?php

namespace App\Domains\Attachment\AuditRecords;

use App\Domains\Attachment\Models\AttachmentModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * The subject is what the file was attached to, not the attachment: clicking the feed line has
 * to lead somewhere a person can read.
 */
class AttachmentUploadedAuditRecord implements AuditRecord
{
    use DescribesSubject, ResolvesActorName;

    public function __construct(
        private readonly AttachmentModel $attachment,
        private readonly Model $attachable,
    ) {}

    public function type(): string
    {
        return 'attachment.uploaded';
    }

    public function title(): string
    {
        return "{$this->actorName()} uploaded «{$this->attachment->original_name}»";
    }

    public function description(): ?string
    {
        return $this->subjectName($this->attachable);
    }

    public function subject(): ?Model
    {
        return $this->attachable;
    }
}
