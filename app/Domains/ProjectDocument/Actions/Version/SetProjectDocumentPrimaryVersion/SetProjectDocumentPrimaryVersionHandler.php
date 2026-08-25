<?php

namespace App\Domains\ProjectDocument\Actions\Version\SetProjectDocumentPrimaryVersion;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentPrimaryVersionChangedAuditRecord;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

class SetProjectDocumentPrimaryVersionHandler
{
    public function handle(SetProjectDocumentPrimaryVersionCommand $command): ProjectDocumentModel
    {
        $command->document->update(['primary_version_id' => $command->versionId]);

        // Re-sending the current choice is not an event.
        if ($command->document->wasChanged('primary_version_id')) {
            AuditTrail::capture(new ProjectDocumentPrimaryVersionChangedAuditRecord(
                $command->document,
                $command->document->primaryVersion,
            ));
        }

        return $command->document;
    }
}
