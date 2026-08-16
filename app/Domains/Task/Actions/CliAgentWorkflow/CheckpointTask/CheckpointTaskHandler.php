<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\CheckpointTask;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Task\AuditRecords\TaskCheckpointAuditRecord;
use App\Libs\AuditTrail\Facades\AuditTrail;

class CheckpointTaskHandler
{
    public function __construct(
        private readonly CreateCommentHandler $createCommentHandler,
    ) {}

    public function handle(CheckpointTaskCommand $command): CommentModel
    {
        $comment = $this->createCommentHandler->handle(new CreateCommentCommand(
            commentable: $command->task,
            author: $command->author,
            content: '# Checkpoint: '.$command->subject."\n\n".$command->comment,
            recordAudit: false,
        ));

        $command->task->touch();

        AuditTrail::capture(new TaskCheckpointAuditRecord($command->task, $command->subject, $command->comment));

        return $comment;
    }
}
