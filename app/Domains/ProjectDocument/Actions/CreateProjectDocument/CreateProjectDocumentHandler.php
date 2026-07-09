<?php

namespace App\Domains\ProjectDocument\Actions\CreateProjectDocument;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\ProjectDocumentKeyResolver;

class CreateProjectDocumentHandler
{
    public function __construct(
        private readonly ProjectDocumentKeyResolver $projectDocumentKeyResolver,
    ) {}

    public function handle(CreateProjectDocumentCommand $command): ProjectDocumentModel
    {
        $documentKey = $this->projectDocumentKeyResolver->resolve($command->project);

        $document = ProjectDocumentModel::create([
            'project_id'      => $command->project->id,
            'parent_id'       => $command->parentId,
            'key'             => $documentKey->value,
            'sequence_number' => $documentKey->sequenceNumber,
            'title'           => $command->title,
            'content'         => $command->content,
        ]);

        if ($command->tagIds !== null) {
            $document->tags()->sync($command->tagIds);
        }

        return $document->refresh();
    }
}
