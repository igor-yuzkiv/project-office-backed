<?php

namespace App\Domains\Comment\Actions\CreateComment;

use App\Domains\Comment\AuditRecords\CommentCreatedAuditRecord;
use App\Domains\Comment\Models\CommentModel;
use App\Infrastructure\Models\Contracts\Commentable;
use App\Libs\AuditTrail\Facades\AuditTrail;
use Illuminate\Database\Eloquent\Model;

class CreateCommentHandler
{
    public function handle(CreateCommentCommand $command): CommentModel
    {
        /** @var CommentModel $comment */
        $comment = $command->commentable->comments()->create([
            'author_id' => $command->author->id,
            'content'   => $command->content,
        ]);

        if ($command->recordAudit) {
            // Every Commentable is an Eloquent model; the contract just does not say so.
            /** @var Commentable&Model $commentable */
            $commentable = $command->commentable;

            AuditTrail::capture(new CommentCreatedAuditRecord($commentable, $command->content));
        }

        return $comment;
    }
}
