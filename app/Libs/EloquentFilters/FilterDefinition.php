<?php

namespace App\Libs\EloquentFilters;

readonly class FilterDefinition
{
    /**
     * @param  class-string<Filter>  $filterClass
     * @param  string[]  $allowedFields
     */
    public function __construct(
        public string $filterClass,
        public array $allowedFields,
    ) {}

    public function key(): string
    {
        return ($this->filterClass)::key();
    }
}
