<?php

namespace App\Http\CliApi\Requests\Concerns;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Validation\Validator;

/**
 * Accepts `task_list_id` as the human key the agent works with (MTM-TL-7) or as a ULID.
 * Route-model binding cannot do this — the value arrives in the request body — so the
 * lookup runs as a validation step and rejects a list from another project.
 */
trait ResolvesTaskList
{
    private ?TaskListModel $resolvedTaskList = null;

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $value = $this->input('task_list_id');

                if (!is_string($value) || $validator->errors()->has('task_list_id')) {
                    return;
                }

                /** @var ProjectModel $project */
                $project = $this->route('project');

                $taskList = TaskListModel::where('project_id', $project->id)
                    ->keyOrId($value)
                    ->first();

                if ($taskList === null) {
                    $validator->errors()->add('task_list_id', "Task list \"{$value}\" was not found in this project.");

                    return;
                }

                $this->resolvedTaskList = $taskList;
            },
        ];
    }

    public function resolvedTaskListId(): ?string
    {
        return $this->resolvedTaskList?->id;
    }
}
