<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\HandoffTask;

use App\Domains\Comment\Actions\CreateComment\CreateCommentCommand;
use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Task\AuditRecords\TaskHandoffAuditRecord;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Libs\AuditTrail\Facades\AuditTrail;
use Illuminate\Support\Facades\DB;

class HandoffTaskHandler
{
    public function __construct(
        private readonly CreateCommentHandler $createCommentHandler,
    ) {}

    public function handle(HandoffTaskCommand $command): TaskModel
    {
        $task = $command->task;

        DB::transaction(function () use ($command, $task): void {
            $this->createCommentHandler->handle(new CreateCommentCommand(
                commentable: $task,
                author: $command->author,
                content: "# Handoff\n\n{$command->resolution}",
                recordAudit: false,
            ));

            $task->update(['status' => TaskStatus::ReadyToTest]);

            AuditTrail::capture(new TaskHandoffAuditRecord($task, $command->resolution));
        });

        return $task->fresh();
    }
}
