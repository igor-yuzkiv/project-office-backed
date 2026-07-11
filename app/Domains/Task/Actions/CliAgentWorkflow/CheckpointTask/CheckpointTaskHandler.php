<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\CheckpointTask;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Comment\Models\CommentModel;

class CheckpointTaskHandler
{
    public function __construct(
        private readonly CreateCommentHandler $createCommentHandler,
    ) {}

    public function handle(CheckpointTaskCommand $command): CommentModel
    {
        $content = [
            '# Checkpoint',
            '## '.$command->subject,
            '## Content',
            $command->comment,
        ];

        $comment = $this->createCommentHandler->handle(new CreateCommentCommand(
            commentable: $command->task,
            author: $command->author,
            content: implode("\n\n", $content),
        ));

        $command->task->touch();

        return $comment;
    }
}
