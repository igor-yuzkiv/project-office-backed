<?php

namespace App\Domains\Task\ValueObjects;

use App\Libs\EloquentFilters\FilterPayload;

readonly class TaskView
{
    /**
     * @param  FilterPayload[]  $filters
     */
    public function __construct(
        public string $key,
        public string $label,
        public array $filters,
    ) {}
}
