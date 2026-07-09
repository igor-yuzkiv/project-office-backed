<?php

namespace App\Http\WebApi\Controllers\Tasks;

use App\Domains\Task\Models\TaskModel;
use App\Http\Shared\Resources\ProjectDocuments\ProjectDocumentOverviewResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskProjectDocumentsController
{
    public function index(TaskModel $task): AnonymousResourceCollection
    {
        $documents = $task->projectDocuments()
            ->orderBy('title')
            ->paginate(perPage: 50);

        return ProjectDocumentOverviewResource::collection($documents);
    }
}
