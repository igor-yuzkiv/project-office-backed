<?php

namespace App\Libs\EloquentFilters\Concerns;

use App\Libs\EloquentFilters\FilterResolver;
use Illuminate\Database\Eloquent\Builder;

/** @phpstan-ignore trait.unused */
trait HasFilters
{
    /** @return array<string, array{0: class-string, 1: array{allowed_fields: string[]}}> */
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
