<?php

namespace App\Domains\TaskList\Actions\UpdateTaskList;

use App\Domains\TaskList\Models\TaskListModel;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTaskListHandler
{
    use AsAction;

    public function handle(UpdateTaskListCommand $command): TaskListModel
    {
        $data = array_filter(
            ['name' => $command->name],
            fn ($value) => $value !== null
        );

        $command->taskList->update($data);

        return $command->taskList->fresh();
    }
}
