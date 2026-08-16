<?php

namespace App\Domains\Dashboard\Services;

use App\Domains\Task\Models\TaskModel;
use App\Domains\Task\Services\TaskViewRegistry;
use App\Domains\Task\ValueObjects\TaskView;
use App\Libs\EloquentFilters\FilterPayload;

class TaskViewCountsService
{
    /**
     * @return array<int, array{key: string, label: string, count: int}>
     */
    public function get(): array
    {
        return array_map(
            fn (TaskView $view): array => [
                'key'   => $view->key,
                'label' => $view->label,
                'count' => TaskModel::query()->filter(array_map($this->toFilterArray(...), $view->filters))->count(),
            ],
            TaskViewRegistry::all(),
        );
    }

    /**
     * scopeFilter() speaks the request payload shape, so registry filters are converted back to it.
     *
     * @return array<string, mixed>
     */
    private function toFilterArray(FilterPayload $filter): array
    {
        return [
            'filter_key' => $filter->filterKey,
            'field_name' => $filter->fieldName,
            'value'      => $filter->value,
            'matchMode'  => $filter->matchMode,
            'params'     => $filter->params,
        ];
    }
}
