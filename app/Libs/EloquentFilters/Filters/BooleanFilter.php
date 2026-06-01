<?php

namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class BooleanFilter extends Filter
{
    public static function key(): string
    {
        return 'boolean';
    }

    public function apply(Builder $query): Builder
    {
        $field = $this->params->get('field');

        if (!$field) {
            return $query;
        }

        return $query->where($field, $this->params->boolean('value'));
    }
}
