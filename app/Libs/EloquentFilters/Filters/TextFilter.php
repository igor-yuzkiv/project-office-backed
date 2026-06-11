<?php

namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use App\Libs\EloquentFilters\MatchMode;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

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
            MatchMode::IN,
            MatchMode::NOT_IN,
        ];
    }

    public function apply(Builder|ScoutBuilder $query): Builder|ScoutBuilder
    {
        $field = $this->payload->fieldName;
        $value = $this->payload->value;
        $matchMode = $this->matchMode(MatchMode::CONTAINS);

        if (!$field) {
            return $query;
        }

        if ($matchMode === MatchMode::IN || $matchMode === MatchMode::NOT_IN) {
            if (!is_array($value) || empty($value)) {
                return $query;
            }

            return $matchMode === MatchMode::IN
                ? $query->whereIn($field, $value)
                : $query->whereNotIn($field, $value);
        }

        if (!$value) {
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
