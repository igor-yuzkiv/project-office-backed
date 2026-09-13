<?php

namespace App\Domains\Task\Queries;

use App\Domains\Task\Models\TaskModel;
use App\Domains\Task\Services\TaskViewRegistry;
use App\Domains\Task\ValueObjects\TaskView;
use App\Domains\Task\ValueObjects\TaskViewCount;
use App\Libs\EloquentFilters\FilterPayload;

class CountTasksPerTaskViewQuery
{
    /**
     * @return TaskViewCount[]
     */
    public function handle(): array
    {
        return array_map(
            fn (TaskView $view): TaskViewCount => new TaskViewCount(
                $view,
                // scopeFilter() speaks the request payload shape, so registry filters are converted back to it.
                TaskModel::query()
                    ->filter(array_map(static fn (FilterPayload $filter): array => $filter->toArray(), $view->filters))
                    ->count(),
            ),
            TaskViewRegistry::all(),
        );
    }
}
