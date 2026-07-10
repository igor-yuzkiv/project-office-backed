<?php

namespace App\Domains\Comment\Actions\DeleteComment;

class DeleteCommentHandler
{
    public function handle(DeleteCommentCommand $command): void
    {
        $command->comment->delete();
    }
}
