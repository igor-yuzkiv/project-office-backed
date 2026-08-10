<?php

namespace App\Http\CliApi\Controllers\TaskLists;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Http\CliApi\Requests\Comment\StoreCommentsRequest;
use App\Http\Shared\Resources\Comment\CommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TaskListCommentsController
{
    public function __construct(
        private readonly CreateCommentHandler $createHandler,
    ) {}

    public function index(ProjectModel $project, TaskListModel $taskList): AnonymousResourceCollection
    {
        $perPage = min(request()->integer('per_page', 10), 100);
        $page = request()->integer('page', 1);
        $comments = $taskList->comments()
            ->with('author')
            ->orderBy('created_at', 'desc')
            ->paginate(
                perPage: $perPage,
                page: $page,
            );

        return CommentResource::collection($comments);
    }

    public function store(ProjectModel $project, TaskListModel $taskList, StoreCommentsRequest $request): JsonResponse
    {
        Gate::authorize('create', CommentModel::class);

        /** @var UserModel $user */
        $user = $request->user();

        $comments = DB::transaction(function () use ($request, $taskList, $user) {
            return collect($request->validated('comments'))
                ->map(function (array $comment) use ($taskList, $user) {
                    $created = $this->createHandler->handle(new CreateCommentCommand(
                        commentable: $taskList,
                        author: $user,
                        content: $comment['content'],
                    ));

                    $created->setRelation('author', $user);

                    return $created;
                });
        });

        return CommentResource::collection($comments)
            ->response()
            ->setStatusCode(201);
    }
}
