<?php

namespace App\Domains\ProjectDocument\Actions\SyncProjectDocumentTasks;

use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\Eloquent\Collection;

class SyncProjectDocumentTasksHandler
{
    /**
     * @return Collection<int, TaskModel>
     */
    public function handle(SyncProjectDocumentTasksCommand $command): Collection
    {
        $command->document->tasks()->sync($command->taskIds);

        /** @var Collection<int, TaskModel> $tasks */
        $tasks = $command->document->tasks()
            ->with(['createdBy', 'updatedBy', 'tags'])
            ->get();

        return $tasks;
    }
}
