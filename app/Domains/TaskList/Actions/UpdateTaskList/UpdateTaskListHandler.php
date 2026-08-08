<?php

namespace App\Domains\TaskList\Actions\UpdateTaskList;

use App\Domains\TaskList\Models\TaskListModel;

class UpdateTaskListHandler
{
    public function handle(UpdateTaskListCommand $command): TaskListModel
    {
        $data = array_filter(
            [
                'name'   => $command->name,
                'status' => $command->status?->value,
            ],
            fn ($value) => $value !== null
        );

        if ($command->descriptionProvided) {
            $data['description'] = $command->description;
        }

        $command->taskList->update($data);

        if ($command->tagIds !== null) {
            $command->taskList->tags()->sync($command->tagIds);
        }

        return $command->taskList->fresh();
    }
}
