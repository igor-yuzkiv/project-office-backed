<?php

namespace App\Http\Controllers\Tasks;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\Comment\CommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TaskCommentsController
{
    public function __construct(
        private readonly CreateCommentHandler $createHandler,
    ) {}

    public function index(TaskModel $task): AnonymousResourceCollection
    {
        $comments = $task->comments()
            ->with('author')
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: 50);

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, TaskModel $task): JsonResponse
    {
        Gate::authorize('create', CommentModel::class);

        /** @var UserModel $user */
        $user = $request->user();

        $comment = $this->createHandler->handle(new CreateCommentCommand(
            task: $task,
            author: $user,
            content: $request->validated('content'),
        ));

        $comment->load('author');

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}
