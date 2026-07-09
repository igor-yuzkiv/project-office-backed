<?php

namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

class TaskFilter extends Filter
{
    public static function key(): string
    {
        return 'tasks';
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

        return $query->whereHas('tasks', fn ($q) => $q->whereIn('tasks.id', $value));
    }
}
