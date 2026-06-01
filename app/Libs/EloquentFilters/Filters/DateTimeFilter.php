<?php

namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use App\Libs\EloquentFilters\MatchMode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

class DateTimeFilter extends Filter
{
    public static function key(): string
    {
        return 'datetime';
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
            MatchMode::DATE_IS,
            MatchMode::DATE_IS_NOT,
            MatchMode::DATE_BEFORE,
            MatchMode::DATE_AFTER,
        ];
    }

    public function apply(Builder|ScoutBuilder $query): Builder|ScoutBuilder
    {
        $field = $this->payload->fieldName;
        $value = $this->payload->value;

        if (!$value) {
            return $query;
        }

        $dateTime = Carbon::parse($value);
        $matchMode = $this->matchMode(MatchMode::EQUALS);

        return match ($matchMode) {
            MatchMode::EQUALS                => $query->where($field, $dateTime),
            MatchMode::NOT_EQUALS            => $query->where($field, '!=', $dateTime),
            MatchMode::GREATER_THAN          => $query->where($field, '>', $dateTime),
            MatchMode::GREATER_THAN_OR_EQUAL => $query->where($field, '>=', $dateTime),
            MatchMode::LESS_THAN             => $query->where($field, '<', $dateTime),
            MatchMode::LESS_THAN_OR_EQUAL    => $query->where($field, '<=', $dateTime),
            MatchMode::DATE_IS               => $query->whereDate($field, $dateTime->toDateString()),
            MatchMode::DATE_IS_NOT           => $query->whereDate($field, '!=', $dateTime->toDateString()),
            MatchMode::DATE_BEFORE           => $query->whereDate($field, '<', $dateTime->toDateString()),
            MatchMode::DATE_AFTER            => $query->whereDate($field, '>', $dateTime->toDateString()),
            default                          => $query,
        };
    }
}
