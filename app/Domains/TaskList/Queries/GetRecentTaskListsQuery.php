<?php

namespace App\Domains\TaskList\Queries;

use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Database\Eloquent\Collection;

class GetRecentTaskListsQuery
{
    /**
     * @return Collection<int, TaskListModel>
     */
    public function handle(int $limit): Collection
    {
        return TaskListModel::query()
            ->withCount('tasks')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }
}
