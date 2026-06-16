<?php

namespace App\Domains\Comment\Actions\DeleteComment;

use App\Domains\Comment\Models\CommentModel;

class DeleteCommentHandler
{
    public function handle(CommentModel $comment): void
    {
        $comment->delete();
    }
}
