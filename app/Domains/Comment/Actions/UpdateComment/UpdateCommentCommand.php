<?php

namespace App\Domains\Comment\Actions\UpdateComment;

use App\Domains\Comment\Models\CommentModel;

class UpdateCommentCommand
{
    public function __construct(
        public readonly CommentModel $comment,
        public readonly string $content,
    ) {}
}
