<?php

namespace App\Domains\ProjectDocument\Actions\Version\CreateProjectDocumentVersion;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentVersionCreatedAuditRecord;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

class CreateProjectDocumentVersionHandler
{
    public function handle(CreateProjectDocumentVersionCommand $command): ProjectDocumentVersionModel
    {
        $source = $command->copyContentFromVersionId !== null
            ? $command->document->versions()->whereKey($command->copyContentFromVersionId)->first()
            : null;

        // Numbers are not reissued in order, so a deleted last version frees its number again.
        // The unique index is what actually guarantees no two versions share one.
        $nextNumber = (int) $command->document->versions()->max('version_number') + 1;

        $version = $command->document->versions()->create([
            'version_number' => $nextNumber,
            'label'          => $command->label,
            'content'        => $source?->content,
            'author_id'      => $command->authorId,
        ]);

        AuditTrail::capture(new ProjectDocumentVersionCreatedAuditRecord($command->document, $version));

        return $version;
    }
}
