<?php

namespace App\Domains\Comment\Actions\CreateComment;

use App\Domains\Comment\Models\CommentModel;

class CreateCommentHandler
{
    public function handle(CreateCommentCommand $command): CommentModel
    {
        /** @var CommentModel $comment */
        $comment = $command->commentable->comments()->create([
            'author_id' => $command->author->id,
            'content'   => $command->content,
        ]);

        return $comment;
    }
}
