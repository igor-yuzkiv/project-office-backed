<?php

namespace App\Domains\ProjectDocument\Actions\UpdateProjectDocument;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class UpdateProjectDocumentHandler
{
    public function handle(UpdateProjectDocumentCommand $command): ProjectDocumentModel
    {
        $command->document->update($command->toModelAttributes());

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
