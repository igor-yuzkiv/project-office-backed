<?php

namespace App\Domains\Task\Queries;

use App\Domains\Task\Models\TaskModel;
use App\Domains\Task\Services\TaskViewRegistry;
use App\Domains\Task\ValueObjects\TaskView;
use App\Libs\EloquentFilters\FilterPayload;

class CountTasksPerTaskViewQuery
{
    /**
     * @return array<int, array{key: string, label: string, count: int}>
     */
    public function handle(): array
    {
        return array_map(
            fn (TaskView $view): array => [
                'key'   => $view->key,
                'label' => $view->label,
                // scopeFilter() speaks the request payload shape, so registry filters are converted back to it.
                'count' => TaskModel::query()
                    ->filter(array_map(static fn (FilterPayload $filter): array => $filter->toArray(), $view->filters))
                    ->count(),
            ],
            TaskViewRegistry::all(),
        );
    }
}
