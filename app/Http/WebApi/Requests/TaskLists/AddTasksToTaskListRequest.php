<?php

namespace App\Http\WebApi\Requests\TaskLists;

use App\Domains\TaskList\Actions\AddTasksToTaskList\AddTasksToTaskListCommand;
use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddTasksToTaskListRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var TaskListModel $taskList */
        $taskList = $this->route('task_list');

        return [
            'task_ids'   => ['required', 'array', 'min:1', 'max:100'],
            'task_ids.*' => [
                'string',
                'ulid',
                'distinct',
                // A candidate belongs to the same project and has no list yet (D3): tasks are
                // never moved between lists through this endpoint.
                Rule::exists('tasks', 'id')
                    ->where('project_id', $taskList->project_id)
                    ->whereNull('task_list_id'),
            ],
        ];
    }

    public function toCommand(TaskListModel $taskList): AddTasksToTaskListCommand
    {
        return new AddTasksToTaskListCommand(
            taskList: $taskList,
            taskIds: $this->validated('task_ids'),
        );
    }
}
