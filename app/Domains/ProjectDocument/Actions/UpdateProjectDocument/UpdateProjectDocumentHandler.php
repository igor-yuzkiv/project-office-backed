<?php

namespace App\Domains\ProjectDocument\Actions\UpdateProjectDocument;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class UpdateProjectDocumentHandler
{
    public function handle(UpdateProjectDocumentCommand $command): ProjectDocumentModel
    {
        if ($command->attributes !== []) {
            $command->document->update($command->attributes);
        }

        if ($command->tagIds !== null) {
            $command->document->tags()->sync($command->tagIds);
        }

        return $command->document->fresh();
    }
}
