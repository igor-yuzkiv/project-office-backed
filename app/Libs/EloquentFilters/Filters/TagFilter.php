<?php

namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

class TagFilter extends Filter
{
    public static function key(): string
    {
        return 'tags';
    }

    public static function requiresFieldName(): bool
    {
        return false;
    }

    public function apply(Builder|ScoutBuilder $query): Builder|ScoutBuilder
    {
        $value = $this->payload->value;

        if (!is_array($value) || empty($value)) {
            return $query;
        }

        return $query->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $value));
    }
}
