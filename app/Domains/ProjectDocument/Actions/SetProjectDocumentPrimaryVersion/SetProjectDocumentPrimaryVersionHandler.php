<?php

namespace App\Domains\ProjectDocument\Actions\SetProjectDocumentPrimaryVersion;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class SetProjectDocumentPrimaryVersionHandler
{
    public function handle(SetProjectDocumentPrimaryVersionCommand $command): ProjectDocumentModel
    {
        $command->document->update(['primary_version_id' => $command->versionId]);

        return $command->document;
    }
}
