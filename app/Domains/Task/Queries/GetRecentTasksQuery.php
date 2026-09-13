<?php

namespace App\Domains\Task\Queries;

use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\Eloquent\Collection;

class GetRecentTasksQuery
{
    /**
     * @return Collection<int, TaskModel>
     */
    public function handle(int $limit): Collection
    {
        return TaskModel::query()
            ->with(['project', 'taskList', 'updatedBy'])
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }
}
