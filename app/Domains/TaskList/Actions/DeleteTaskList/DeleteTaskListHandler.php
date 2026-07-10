<?php

namespace App\Domains\TaskList\Actions\DeleteTaskList;

class DeleteTaskListHandler
{
    public function handle(DeleteTaskListCommand $command): void
    {
        $command->taskList->delete();
    }
}
