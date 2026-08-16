<?php

namespace App\Domains\Dashboard\Services;

use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\Eloquent\Collection;

class RecentTasksService
{
    private const LIMIT = 8;

    /**
     * @return Collection<int, TaskModel>
     */
    public function get(): Collection
    {
        return TaskModel::query()
            ->with('project')
            ->orderByDesc('updated_at')
            ->limit(self::LIMIT)
            ->get();
    }
}
