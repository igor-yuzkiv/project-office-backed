<?php

namespace App\Domains\TaskList\Queries;

use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CountTasksPerStatusQuery
{
    /**
     * Counts tasks by status for each task list in one grouped query; every status is present, missing ones as 0.
     *
     * @param  array<string>  $taskListIds
     * @return array<string, array<string, int>>
     */
    public function handle(array $taskListIds): array
    {
        $taskListIds = array_values(array_unique($taskListIds));
        if ($taskListIds === []) {
            return [];
        }

        $empty = array_fill_keys(array_column(TaskStatus::cases(), 'value'), 0);
        $counts = array_fill_keys($taskListIds, $empty);

        $rows = TaskModel::query()
            ->select('task_list_id', 'status', DB::raw('count(*) as aggregate'))
            ->whereIn('task_list_id', $taskListIds)
            ->groupBy('task_list_id', 'status')
            ->toBase()
            ->get();

        foreach ($rows as $row) {
            $counts[$row->task_list_id][$row->status] = (int) $row->aggregate;
        }

        return $counts;
    }

    /**
     * Sets `task_status_counts` on every given list from one query, so a page of lists — or the
     * lists nested in a page of projects — never counts per row.
     *
     * @param  Collection<int, TaskListModel>  $taskLists
     */
    public function attach(Collection $taskLists): void
    {
        $counts = $this->handle($taskLists->map(fn (TaskListModel $taskList) => $taskList->id)->all());

        foreach ($taskLists as $taskList) {
            $taskList->setAttribute('task_status_counts', $counts[$taskList->id]);
        }
    }
}
