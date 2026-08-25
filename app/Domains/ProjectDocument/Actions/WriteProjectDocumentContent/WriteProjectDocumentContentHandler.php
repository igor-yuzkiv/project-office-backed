<?php

namespace App\Domains\ProjectDocument\Actions\WriteProjectDocumentContent;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentUpdatedAuditRecord;
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
        } else {
            $version->update(['content' => $command->content]);
        }

        if ($command->recordsUpdate) {
            // The content lives on the version row, so writing it leaves the document itself
            // clean — without this the document's `updated_at`, `updated_by`, and activity stream
            // would go stale on the surface where most document writing happens.
            $command->document->touch();

            AuditTrail::capture(new ProjectDocumentUpdatedAuditRecord($command->document));
        }

        return $version;
    }
}
