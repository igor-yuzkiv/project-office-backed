<?php

namespace App\Domains\Dashboard\Services;

use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Database\Eloquent\Collection;

class RecentTaskListsService
{
    private const LIMIT = 4;

    /**
     * @return Collection<int, TaskListModel>
     */
    public function get(): Collection
    {
        return TaskListModel::query()
            ->withCount('tasks')
            ->orderByDesc('updated_at')
            ->limit(self::LIMIT)
            ->get();
    }
}
