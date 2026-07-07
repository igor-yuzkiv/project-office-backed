<?php

namespace App\Http\CliApi\Controllers\Tasks;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Comment\Actions\UpdateComment\UpdateCommentCommand;
use App\Domains\Comment\Actions\UpdateComment\UpdateCommentHandler;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Http\CliApi\Requests\Comment\StoreCommentsRequest;
use App\Http\CliApi\Requests\Comment\UpdateCommentRequest;
use App\Http\Shared\Resources\Comment\CommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TaskCommentsController
{
    public function __construct(
        private readonly CreateCommentHandler $createHandler,
        private readonly UpdateCommentHandler $updateHandler,
    ) {}

    public function index(ProjectModel $project, TaskModel $task): AnonymousResourceCollection
    {
        $perPage = min(request()->integer('per_page', 10), 100);
        $page = request()->integer('page', 1);
        $comments = $task->comments()
            ->with('author')
            ->orderBy('created_at', 'desc')
            ->paginate(
                perPage: $perPage,
                page: $page,
            );

        return CommentResource::collection($comments);
    }

    public function store(ProjectModel $project, TaskModel $task, StoreCommentsRequest $request): JsonResource
    {
        Gate::authorize('create', CommentModel::class);

        /** @var UserModel $user */
        $user = $request->user();

        $comments = DB::transaction(function () use ($request, $task, $user) {
            return collect($request->validated('comments'))
                ->map(function (array $comment) use ($task, $user) {
                    $created = $this->createHandler->handle(new CreateCommentCommand(
                        task: $task,
                        author: $user,
                        content: $comment['content'],
                    ));

                    $created->setRelation('author', $user);

                    return $created;
                });
        });

        // Return type is fixed to JsonResource, so the 201 status is applied via
        // an AnonymousResourceCollection override instead of ->response()->setStatusCode(),
        // which would return a JsonResponse and violate the contract.
        return new class($comments, CommentResource::class) extends AnonymousResourceCollection
        {
            public function withResponse(Request $request, JsonResponse $response): void
            {
                $response->setStatusCode(201);
            }
        };
    }

    public function update(ProjectModel $project, TaskModel $task, CommentModel $comment, UpdateCommentRequest $request): JsonResource
    {
        abort_unless($task->comments()->whereKey($comment->id)->exists(), 404);

        Gate::authorize('update', $comment);

        $comment = $this->updateHandler->handle(new UpdateCommentCommand(
            comment: $comment,
            content: $request->validated('content'),
        ));

        $comment->load('author');

        return new CommentResource($comment);
    }
}
