<?php

namespace App\Http\CliApi\Controllers\Tasks;

use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Actions\CliAgentWorkflow\CheckpointTask\CheckpointTaskHandler;
use App\Domains\Task\Actions\CliAgentWorkflow\HandoffTask\HandoffTaskHandler;
use App\Domains\Task\Actions\CliAgentWorkflow\StartTask\StartTaskHandler;
use App\Domains\Task\Models\TaskModel;
use App\Http\CliApi\Requests\Tasks\CheckpointTaskRequest;
use App\Http\CliApi\Requests\Tasks\HandoffTaskRequest;
use App\Http\CliApi\Requests\Tasks\StartTaskRequest;
use App\Http\Shared\Resources\Comment\CommentResource;
use App\Http\Shared\Resources\Tasks\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TaskAgenticWorkflowController
{
    public function __construct(
        private readonly StartTaskHandler $startHandler,
        private readonly CheckpointTaskHandler $checkpointHandler,
        private readonly HandoffTaskHandler $handoffHandler,
    ) {}

    public function start(ProjectModel $project, TaskModel $task, StartTaskRequest $request): JsonResponse
    {
        $task = $this->startHandler->handle($request->toCommand($task));
        $task->load(['createdBy', 'updatedBy', 'project', 'taskList', 'tags']);

        return response()->json([
            'task'     => new TaskResource($task),
            'comments' => CommentResource::collection($task->comments),
        ]);
    }

    public function checkpoint(ProjectModel $project, TaskModel $task, CheckpointTaskRequest $request): JsonResponse
    {
        Gate::authorize('create', CommentModel::class);

        $comment = $this->checkpointHandler->handle($request->toCommand($task));
        $comment->load('author');

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    public function handoff(ProjectModel $project, TaskModel $task, HandoffTaskRequest $request): JsonResponse
    {
        Gate::authorize('create', CommentModel::class);

        $task = $this->handoffHandler->handle($request->toCommand($task));
        $task->load(['createdBy', 'updatedBy']);

        return (new TaskResource($task))->response();
    }
}
