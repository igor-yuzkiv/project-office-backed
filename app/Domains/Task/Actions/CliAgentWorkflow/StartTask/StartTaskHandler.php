<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\StartTask;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;

class StartTaskHandler
{
    public function __construct(
        private readonly CreateCommentHandler $createCommentHandler,
    ) {}

    public function handle(StartTaskCommand $command): TaskModel
    {
        $task = $command->task;

        if ($task->status !== TaskStatus::InProgress) {
            $task->update(['status' => TaskStatus::InProgress]);
        }

        if ($command->comment !== null) {
            $this->createCommentHandler->handle(new CreateCommentCommand(
                commentable: $task,
                author: $command->author,
                content: "# Start\n\n{$command->comment}",
            ));
        }

        $task->setRelation(
            'comments',
            $task->comments()->with('author')->latest()->limit($command->commentsLimit)->get()
        );

        return $task;
    }
}
