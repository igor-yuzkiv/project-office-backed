<?php

namespace App\Http\WebApi\Controllers\TaskViews;

use App\Domains\Task\Queries\CountTasksPerTaskViewQuery;
use App\Http\Shared\Resources\TaskViews\TaskViewResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskViewsController
{
    public function __construct(
        private readonly CountTasksPerTaskViewQuery $countTasksPerTaskView,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return TaskViewResource::collection($this->countTasksPerTaskView->handle());
    }
}
