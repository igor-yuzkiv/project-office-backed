<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\StartTask;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Task\AuditRecords\TaskStartedAuditRecord;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Libs\AuditTrail\Facades\AuditTrail;

class StartTaskHandler
{
    public function __construct(
        private readonly CreateCommentHandler $createCommentHandler,
    ) {}

    public function handle(StartTaskCommand $command): TaskModel
    {
        $task = $command->task;

        // Only a real transition is an event: task:start also resumes work and loads context,
        // so a task worked on across sessions would otherwise repeat the same feed line.
        if ($task->status !== TaskStatus::InProgress) {
            $task->update(['status' => TaskStatus::InProgress]);

            AuditTrail::capture(new TaskStartedAuditRecord($task));
        }

        if ($command->comment !== null) {
            $this->createCommentHandler->handle(new CreateCommentCommand(
                commentable: $task,
                author: $command->author,
                content: "# Start\n\n{$command->comment}",
                recordAudit: false,
            ));
        }

        $task->setRelation(
            'comments',
            $task->comments()->with('author')->latest()->limit($command->commentsLimit)->get()
        );

        return $task;
    }
}
