<?php

namespace App\Http\WebApi\Controllers\Comment;

use App\Domains\Comment\Actions\DeleteComment\DeleteCommentHandler;
use App\Domains\Comment\Actions\UpdateComment\UpdateCommentCommand;
use App\Domains\Comment\Actions\UpdateComment\UpdateCommentHandler;
use App\Domains\Comment\Models\CommentModel;
use App\Http\Shared\Resources\Comment\CommentResource;
use App\Http\WebApi\Requests\Comment\UpdateCommentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class CommentController
{
    public function __construct(
        private readonly UpdateCommentHandler $updateHandler,
        private readonly DeleteCommentHandler $deleteHandler,
    ) {}

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
