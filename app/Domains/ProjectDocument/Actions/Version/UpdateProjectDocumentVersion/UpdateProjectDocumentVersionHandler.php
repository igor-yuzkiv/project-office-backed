<?php

namespace App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersion;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentVersionUpdatedAuditRecord;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

class UpdateProjectDocumentVersionHandler
{
    public function handle(UpdateProjectDocumentVersionCommand $command): ProjectDocumentVersionModel
    {
        $version = $command->version;

        $version->update(['label' => $command->label]);

        $changed = ProjectDocumentVersionUpdatedAuditRecord::reportableColumns(array_keys($version->getChanges()));

        if ($changed !== []) {
            /** @var ProjectDocumentModel $document */
            $document = $version->document;

            AuditTrail::capture(new ProjectDocumentVersionUpdatedAuditRecord($document, $version, $changed));
            $document->touch();
        }

        return $version;
    }
}
