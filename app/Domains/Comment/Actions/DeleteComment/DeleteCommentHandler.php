<?php

namespace App\Domains\Comment\Actions\DeleteComment;

use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCommentHandler
{
    use AsAction;

    public function handle(DeleteCommentCommand $command): void
    {
        $command->comment->delete();
    }
}
