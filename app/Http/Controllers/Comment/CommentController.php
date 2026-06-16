<?php

namespace App\Http\Controllers\Comment;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Comment\Actions\DeleteComment\DeleteCommentHandler;
use App\Domains\Comment\Actions\UpdateComment\UpdateCommentCommand;
use App\Domains\Comment\Actions\UpdateComment\UpdateCommentHandler;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\Comment\CommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CommentController
{
    public function __construct(
        private readonly CreateCommentHandler $createHandler,
        private readonly UpdateCommentHandler $updateHandler,
        private readonly DeleteCommentHandler $deleteHandler,
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

    public function update(UpdateCommentRequest $request, CommentModel $comment): CommentResource
    {
        Gate::authorize('update', $comment);

        $comment = $this->updateHandler->handle(new UpdateCommentCommand(
            comment: $comment,
            content: $request->validated('content'),
        ));

        $comment->load('author');

        return new CommentResource($comment);
    }

    public function destroy(CommentModel $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        $this->deleteHandler->handle($comment);

        return response()->json(['message' => 'Comment deleted.']);
    }
}
