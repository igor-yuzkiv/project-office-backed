<?php

namespace App\Domains\TaskList\Actions\DeleteTaskList;

use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTaskListHandler
{
    use AsAction;

    public function handle(DeleteTaskListCommand $command): void
    {
        $command->taskList->delete();
    }
}
