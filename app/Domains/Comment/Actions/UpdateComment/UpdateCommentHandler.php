<?php

namespace App\Domains\Comment\Actions\UpdateComment;

use App\Domains\Comment\Models\CommentModel;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCommentHandler
{
    use AsAction;

    public function handle(UpdateCommentCommand $command): CommentModel
    {
        $command->comment->update([
            'content' => $command->content,
        ]);

        return $command->comment;
    }
}
