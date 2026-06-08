<?php

namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use App\Libs\EloquentFilters\MatchMode;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

class LookupFilter extends Filter
{
    public static function key(): string
    {
        return 'lookup';
    }

    public static function supportedMatchModes(): ?array
    {
        return [
            MatchMode::EQUALS,
            MatchMode::NOT_EQUALS,
        ];
    }

    public function apply(Builder|ScoutBuilder $query): Builder|ScoutBuilder
    {
        $field = $this->payload->fieldName;
        $value = $this->payload->value;
        $matchMode = $this->matchMode(MatchMode::EQUALS);

        if (!$field || !is_string($value) || $value === '') {
            return $query;
        }

        return match ($matchMode) {
            MatchMode::EQUALS     => $query->where($field, $value),
            MatchMode::NOT_EQUALS => $query->where($field, '!=', $value),
            default               => $query,
        };
    }
}
