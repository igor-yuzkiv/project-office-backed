<?php

namespace App\Http\Shared\Resources\TaskViews;

use App\Domains\Task\ValueObjects\TaskViewCount;
use App\Libs\EloquentFilters\FilterPayload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TaskViewCount */
class TaskViewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key'     => $this->view->key,
            'label'   => $this->view->label,
            'filters' => array_map(
                static fn (FilterPayload $filter): array => $filter->toArray(),
                $this->view->filters,
            ),
            'count' => $this->count,
        ];
    }
}
