<?php

namespace App\Http\Controllers\Tasks;

use App\Domains\Task\Actions\SyncTaskOwners\SyncTaskOwnersHandler;
use App\Domains\Task\Models\TaskModel;
use App\Http\Requests\Tasks\SyncTaskOwnersRequest;
use App\Http\Resources\Tasks\TaskOwnerResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskOwnersController
{
    public function __construct(
        private readonly SyncTaskOwnersHandler $syncHandler,
    ) {}

    public function index(TaskModel $task): AnonymousResourceCollection
    {
        $task->load('taskOwners.user');

        return TaskOwnerResource::collection($task->taskOwners);
    }

    public function sync(SyncTaskOwnersRequest $request, TaskModel $task): AnonymousResourceCollection
    {
        $owners = $this->syncHandler->handle($request->toCommand($task->id));

        return TaskOwnerResource::collection($owners);
    }
}
