<?php

namespace App\Domains\Task\Actions\DeleteTask;

use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTaskHandler
{
    use AsAction;

    public function handle(DeleteTaskCommand $command): void
    {
        $command->task->delete();
    }
}
