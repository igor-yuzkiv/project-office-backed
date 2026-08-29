<?php

namespace App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersionContent;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentVersionUpdatedAuditRecord;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

class UpdateProjectDocumentVersionContentHandler
{
    public function handle(UpdateProjectDocumentVersionContentCommand $command): ProjectDocumentVersionModel
    {
        $version = $command->version;

        $version->update(['content' => $command->content]);

        // Content sent back unchanged — an autosave that found nothing new — produces no event.
        $changed = ProjectDocumentVersionUpdatedAuditRecord::reportableColumns(array_keys($version->getChanges()));

        if ($changed !== []) {
            /** @var ProjectDocumentModel $document */
            $document = $version->document;

            AuditTrail::capture(new ProjectDocumentVersionUpdatedAuditRecord($document, $version, $changed));
            // The content lives on the version row, so without this the document's own
            // `updated_at` and `updated_by` would go stale while its text keeps changing.
            $document->touch();
        }

        return $version;
    }
}
