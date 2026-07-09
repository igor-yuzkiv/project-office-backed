<?php

namespace App\Domains\ProjectDocument\Actions\SyncProjectDocumentTasks;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;

class SyncProjectDocumentTasksCommand
{
    /**
     * @param  string[]  $taskIds
     */
    public function __construct(
        public readonly ProjectDocumentModel $document,
        public readonly array $taskIds,
    ) {}
}
