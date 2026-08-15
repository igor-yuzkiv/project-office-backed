<?php

namespace App\Domains\ProjectDocument\Actions\CreateProjectDocument;

use App\Domains\ProjectDocument\AuditRecords\ProjectDocumentCreatedAuditRecord;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\ProjectDocument\ProjectDocumentKeyResolver;
use App\Libs\AuditTrail\Facades\AuditTrail;

class CreateProjectDocumentHandler
{
    public function __construct(
        private readonly ProjectDocumentKeyResolver $projectDocumentKeyResolver,
    ) {}

    public function handle(CreateProjectDocumentCommand $command): ProjectDocumentModel
    {
        $documentKey = $this->projectDocumentKeyResolver->resolve($command->project);

        $document = ProjectDocumentModel::create($command->toModelAttributes($documentKey));

        $this->syncTags($document, $command);

        if ($command->recordAudit) {
            AuditTrail::capture(new ProjectDocumentCreatedAuditRecord($document, (string) $command->project->name));
        }

        return $document->refresh();
    }

    private function syncTags(ProjectDocumentModel $document, CreateProjectDocumentCommand $command): void
    {
        if ($command->tagIds !== null) {
            $document->tags()->sync($command->tagIds);
        }
    }
}
