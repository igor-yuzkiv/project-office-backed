<?php

namespace App\Domains\ProjectDocument\Actions\SyncProjectDocumentTasks;

use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncProjectDocumentTasksHandler
{
    use AsAction;

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
