<?php

namespace App\Http\Shared\Resources\TaskViews;

use App\Domains\Task\ValueObjects\TaskView;
use App\Libs\EloquentFilters\FilterPayload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TaskView */
class TaskViewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key'     => $this->key,
            'label'   => $this->label,
            'filters' => array_map(
                static fn (FilterPayload $filter): array => [
                    'filter_key' => $filter->filterKey,
                    'field_name' => $filter->fieldName,
                    'value'      => $filter->value,
                    'matchMode'  => $filter->matchMode,
                    'params'     => $filter->params,
                ],
                $this->filters,
            ),
        ];
    }
}
