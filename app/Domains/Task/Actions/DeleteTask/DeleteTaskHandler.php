<?php

namespace App\Domains\Task\Actions\DeleteTask;

class DeleteTaskHandler
{
    public function handle(DeleteTaskCommand $command): void
    {
        $command->task->delete();
    }
}
