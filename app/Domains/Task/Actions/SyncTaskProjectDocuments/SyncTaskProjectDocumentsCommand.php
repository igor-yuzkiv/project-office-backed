<?php

namespace App\Domains\Task\Actions\SyncTaskProjectDocuments;

use App\Domains\Task\Models\TaskModel;

class SyncTaskProjectDocumentsCommand
{
    /**
     * @param  string[]  $documentIds
     */
    public function __construct(
        public readonly TaskModel $task,
        public readonly array $documentIds,
    ) {}
}
