<?php

namespace App\Domains\ProjectDocument\Actions\Version\DeleteProjectDocumentVersion;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentVersionDeletedAuditRecord;
use App\Libs\AuditTrail\Facades\AuditTrail;
use Illuminate\Support\Facades\DB;

class DeleteProjectDocumentVersionHandler
{
    /**
     * Deleting the last version is allowed: a document without versions is an ordinary state.
     * A pin pointing at the deleted version is cleared by the foreign key, not here.
     */
    public function handle(DeleteProjectDocumentVersionCommand $command): void
    {
        $version = $command->version;
        $document = $version->document;
        $versionNumber = $version->version_number;
        $label = $version->label;

        DB::transaction(function () use ($version): void {
            $version->annotations()->delete();
            $version->delete();
        });

        AuditTrail::capture(new ProjectDocumentVersionDeletedAuditRecord($document, $versionNumber, $label));
    }
}
