<?php

namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

class BooleanFilter extends Filter
{
    public static function key(): string
    {
        return 'boolean';
    }

    public function apply(Builder|ScoutBuilder $query): Builder|ScoutBuilder
    {
        $field = $this->payload->fieldName;

        return $query->where($field, filter_var($this->payload->value, FILTER_VALIDATE_BOOLEAN));
    }
}
