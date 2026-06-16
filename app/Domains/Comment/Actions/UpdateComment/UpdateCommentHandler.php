<?php

namespace App\Domains\Comment\Actions\UpdateComment;

use App\Domains\Comment\Models\CommentModel;

class UpdateCommentHandler
{
    public function handle(UpdateCommentCommand $command): CommentModel
    {
        $command->comment->update([
            'content' => $command->content,
        ]);

        return $command->comment;
    }
}
