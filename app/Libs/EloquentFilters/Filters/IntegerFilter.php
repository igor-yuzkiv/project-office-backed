<?php

namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use App\Libs\EloquentFilters\MatchMode;
use Illuminate\Database\Eloquent\Builder;

class IntegerFilter extends Filter
{
    public static function key(): string
    {
        return 'integer';
    }

    public static function supportedMatchModes(): ?array
    {
        return [
            MatchMode::EQUALS,
            MatchMode::NOT_EQUALS,
            MatchMode::GREATER_THAN,
            MatchMode::GREATER_THAN_OR_EQUAL,
            MatchMode::LESS_THAN,
            MatchMode::LESS_THAN_OR_EQUAL,
        ];
    }

    public function apply(Builder $query): Builder
    {
        $field = $this->params->get('field');
        $value = $this->params->get('value');
        $matchMode = $this->matchMode(MatchMode::EQUALS);

        if (!$field || !is_numeric($value)) {
            return $query;
        }

        $intValue = (int) $value;

        return match ($matchMode) {
            MatchMode::EQUALS                => $query->where($field, $intValue),
            MatchMode::NOT_EQUALS            => $query->where($field, '!=', $intValue),
            MatchMode::GREATER_THAN          => $query->where($field, '>', $intValue),
            MatchMode::GREATER_THAN_OR_EQUAL => $query->where($field, '>=', $intValue),
            MatchMode::LESS_THAN             => $query->where($field, '<', $intValue),
            MatchMode::LESS_THAN_OR_EQUAL    => $query->where($field, '<=', $intValue),
            default                          => $query,
        };
    }
}
