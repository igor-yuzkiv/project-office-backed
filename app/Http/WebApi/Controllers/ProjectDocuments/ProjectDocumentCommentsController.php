<?php

namespace App\Http\WebApi\Controllers\ProjectDocuments;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\User\Models\UserModel;
use App\Http\Shared\Resources\Comment\CommentResource;
use App\Http\WebApi\Requests\Comment\StoreCommentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ProjectDocumentCommentsController
{
    public function __construct(
        private readonly CreateCommentHandler $createHandler,
    ) {}

    public function index(ProjectDocumentModel $projectDocument): AnonymousResourceCollection
    {
        $comments = $projectDocument->comments()
            ->with('author')
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: 50);

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, ProjectDocumentModel $projectDocument): JsonResponse
    {
        Gate::authorize('create', CommentModel::class);

        /** @var UserModel $user */
        $user = $request->user();

        $comment = $this->createHandler->handle(new CreateCommentCommand(
            commentable: $projectDocument,
            author: $user,
            content: $request->validated('content'),
        ));

        $comment->load('author');

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}
