<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\HandoffTask;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Support\Facades\DB;

class HandoffTaskHandler
{
    public function __construct(
        private readonly CreateCommentHandler $createCommentHandler,
    ) {}

    public function handle(HandoffTaskCommand $command): TaskModel
    {
        $task = $command->task;

        $content = [
            '# Handoff',
            '## Resolution',
            $command->resolution,
        ];

        DB::transaction(function () use ($command, $task, $content): void {
            $this->createCommentHandler->handle(new CreateCommentCommand(
                commentable: $task,
                author: $command->author,
                content: implode("\n\n", $content),
            ));

            $task->update(['status' => TaskStatus::ReadyToTest]);
        });

        return $task->fresh();
    }
}
