<?php

namespace App\Domains\ProjectDocument\Actions\Version\WriteProjectDocumentContent;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentVersionCreatedAuditRecord;
use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentVersionUpdatedAuditRecord;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

/**
 * Writes content without any notion of versions on the caller's side — the surface the CLI API is
 * built on, where a document still looks like a single body of text.
 */
class WriteProjectDocumentContentHandler
{
    public function handle(WriteProjectDocumentContentCommand $command): ProjectDocumentVersionModel
    {
        $version = $command->document->effectiveVersion();

        if ($version === null) {
            $version = $command->document->versions()->create([
                'version_number' => 1,
                'content'        => $command->content,
                'author_id'      => $command->authorId,
            ]);

            AuditTrail::capture(new ProjectDocumentVersionCreatedAuditRecord($command->document, $version));
        } else {
            $version->update(['content' => $command->content]);

            $changed = ProjectDocumentVersionUpdatedAuditRecord::reportableColumns(array_keys($version->getChanges()));

            if ($changed !== []) {
                AuditTrail::capture(new ProjectDocumentVersionUpdatedAuditRecord($command->document, $version, $changed));
            }
        }

        // The content lives on the version row, so without this the document's own `updated_at`
        // and `updated_by` would go stale while its text keeps changing.
        $command->document->touch();

        return $version;
    }
}
