<?php

namespace App\Domains\ProjectDocument\Actions\Document\UpdateProjectDocument;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentUpdatedAuditRecord;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

class UpdateProjectDocumentHandler
{
    public function handle(UpdateProjectDocumentCommand $command): ProjectDocumentModel
    {
        $command->document->update($command->toModelAttributes());

        if ($command->document->wasChanged()) {
            AuditTrail::capture(new ProjectDocumentUpdatedAuditRecord($command->document));
        }

        $this->syncTags($command);

        return $command->document->fresh();
    }

    private function syncTags(UpdateProjectDocumentCommand $command): void
    {
        if ($command->tagIds !== null) {
            $command->document->tags()->sync($command->tagIds);
        }
    }
}
