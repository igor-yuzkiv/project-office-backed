<?php

namespace App\Domains\Task\Actions\SyncTaskProjectDocuments;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Database\Eloquent\Collection;

class SyncTaskProjectDocumentsHandler
{
    /**
     * @return Collection<int, ProjectDocumentModel>
     */
    public function handle(SyncTaskProjectDocumentsCommand $command): Collection
    {
        $command->task->projectDocuments()->sync($command->documentIds);

        /** @var Collection<int, ProjectDocumentModel> $documents */
        $documents = $command->task->projectDocuments()
            ->with(['tags'])
            ->get();

        return $documents;
    }
}
