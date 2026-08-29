<?php

namespace App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersionContent;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentVersionUpdatedAuditRecord;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Libs\AuditTrail\Facades\AuditTrail;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateProjectDocumentVersionContentHandler
{
    public function handle(UpdateProjectDocumentVersionContentCommand $command): ProjectDocumentVersionModel
    {
        $version = $command->version;

        // The route binds the two independently, so a version reached through the wrong document
        // is treated as not there — the same answer the bulk write gives through its relation.
        if ($version->project_document_id !== $command->document->id) {
            throw (new ModelNotFoundException)->setModel($version::class, [$version->id]);
        }

        $version->update(['content' => $command->content]);

        // Content sent back unchanged — an autosave that found nothing new — produces no event.
        $changed = ProjectDocumentVersionUpdatedAuditRecord::reportableColumns(array_keys($version->getChanges()));

        if ($changed !== []) {
            AuditTrail::capture(new ProjectDocumentVersionUpdatedAuditRecord($command->document, $version, $changed));
        }

        // The content lives on the version row, so without this the document's own
        // `updated_at` and `updated_by` would go stale while its text keeps changing.
        $command->document->touch();

        return $version;
    }
}
