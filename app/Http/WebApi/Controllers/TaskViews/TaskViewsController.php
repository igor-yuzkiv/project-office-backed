<?php

namespace App\Http\WebApi\Controllers\TaskViews;

use App\Domains\Task\Services\TaskViewRegistry;
use App\Http\Shared\Resources\TaskViews\TaskViewResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskViewsController
{
    public function index(): AnonymousResourceCollection
    {
        return TaskViewResource::collection(TaskViewRegistry::all());
    }
}
