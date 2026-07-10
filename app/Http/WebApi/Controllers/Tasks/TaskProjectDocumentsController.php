<?php

namespace App\Http\WebApi\Controllers\Tasks;

use App\Domains\Task\Actions\SyncTaskProjectDocuments\SyncTaskProjectDocumentsHandler;
use App\Domains\Task\Models\TaskModel;
use App\Http\Shared\Resources\ProjectDocuments\ProjectDocumentOverviewResource;
use App\Http\WebApi\Requests\Tasks\SyncTaskProjectDocumentsRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskProjectDocumentsController
{
    public function __construct(
        private readonly SyncTaskProjectDocumentsHandler $syncHandler,
    ) {}

    public function index(TaskModel $task): AnonymousResourceCollection
    {
        $documents = $task->projectDocuments()
            ->with(['tags', 'updatedBy'])
            ->orderBy('title')
            ->paginate(perPage: 50);

        return ProjectDocumentOverviewResource::collection($documents);
    }

    public function sync(SyncTaskProjectDocumentsRequest $request, TaskModel $task): AnonymousResourceCollection
    {
        $documents = $this->syncHandler->handle($request->toCommand($task));

        return ProjectDocumentOverviewResource::collection($documents);
    }
}
