<?php

namespace App\Http\WebApi\Controllers\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\SyncProjectDocumentTasks\SyncProjectDocumentTasksHandler;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Http\Shared\Resources\Tasks\TaskOverviewResource;
use App\Http\WebApi\Requests\ProjectDocuments\SyncProjectDocumentTasksRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectDocumentTasksController
{
    public function __construct(
        private readonly SyncProjectDocumentTasksHandler $syncHandler,
    ) {}

    public function index(ProjectDocumentModel $projectDocument): AnonymousResourceCollection
    {
        $tasks = $projectDocument->tasks()
            ->with(['tags'])
            ->orderBy('name')
            ->paginate(perPage: 50);

        return TaskOverviewResource::collection($tasks);
    }

    public function sync(SyncProjectDocumentTasksRequest $request, ProjectDocumentModel $projectDocument): AnonymousResourceCollection
    {
        $tasks = $this->syncHandler->handle($request->toCommand($projectDocument));

        return TaskOverviewResource::collection($tasks);
    }
}
