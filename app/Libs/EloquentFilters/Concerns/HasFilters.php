<?php

namespace App\Libs\EloquentFilters\Concerns;

use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\FilterResolver;
use Illuminate\Database\Eloquent\Builder;

trait HasFilters
{
    /** @return FilterDefinition[] */
    abstract public static function allowedFilters(): array;

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $resolver = new FilterResolver;
        $allowedFilters = static::allowedFilters();

        foreach ($filters as $payload) {
            $filter = $resolver->resolve($payload, $allowedFilters);
            $query = $filter->apply($query);
        }

        return $query;
    }
}
