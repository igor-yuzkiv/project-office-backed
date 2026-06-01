<?php

namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use App\Libs\EloquentFilters\MatchMode;
use Illuminate\Database\Eloquent\Builder;

class TextFilter extends Filter
{
    public static function key(): string
    {
        return 'text';
    }

    public static function supportedMatchModes(): ?array
    {
        return [
            MatchMode::EQUALS,
            MatchMode::NOT_EQUALS,
            MatchMode::STARTS_WITH,
            MatchMode::ENDS_WITH,
            MatchMode::CONTAINS,
            MatchMode::NOT_CONTAINS,
        ];
    }

    public function apply(Builder $query): Builder
    {
        $field = $this->params->get('field');
        $value = $this->params->get('value');
        $matchMode = $this->matchMode(MatchMode::CONTAINS);

        if (!$field || !$value) {
            return $query;
        }

        return match ($matchMode) {
            MatchMode::EQUALS       => $query->where($field, $value),
            MatchMode::NOT_EQUALS   => $query->where($field, '!=', $value),
            MatchMode::STARTS_WITH  => $query->whereLike($field, "{$value}%"),
            MatchMode::ENDS_WITH    => $query->whereLike($field, "%{$value}"),
            MatchMode::CONTAINS     => $query->whereLike($field, "%{$value}%"),
            MatchMode::NOT_CONTAINS => $query->whereNotLike($field, "%{$value}%"),
            default                 => $query,
        };
    }
}
