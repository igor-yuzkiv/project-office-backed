<?php

namespace App\Domains\ProjectDocument\Actions\CreateProjectDocument;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class CreateProjectDocumentHandler
{
    public function handle(CreateProjectDocumentCommand $command): ProjectDocumentModel
    {
        $document = ProjectDocumentModel::create([
            'project_id' => $command->projectId,
            'parent_id'  => null,
            'title'      => $command->title,
        ]);

        if ($command->tagIds !== null) {
            $document->tags()->sync($command->tagIds);
        }

        return $document->refresh();
    }
}
