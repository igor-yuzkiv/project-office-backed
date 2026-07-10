<?php

namespace App\Domains\Comment\Actions\DeleteComment;

use App\Domains\Comment\Models\CommentModel;

class DeleteCommentCommand
{
    public function __construct(
        public readonly CommentModel $comment,
    ) {}
}
