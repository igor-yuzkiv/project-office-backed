<?php

namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use App\Libs\EloquentFilters\MatchMode;
use Illuminate\Database\Eloquent\Builder;

class NullableFilter extends Filter
{
    public static function key(): string
    {
        return 'nullable';
    }

    public static function supportedMatchModes(): ?array
    {
        return [
            MatchMode::EQUALS,
            MatchMode::NOT_EQUALS,
        ];
    }

    public function apply(Builder $query): Builder
    {
        $field = $this->params->get('field');

        if (!$field) {
            return $query;
        }

        $matchMode = $this->matchMode(MatchMode::EQUALS);

        return match ($matchMode) {
            MatchMode::EQUALS     => $query->whereNull($field),
            MatchMode::NOT_EQUALS => $query->whereNotNull($field),
            default               => $query,
        };
    }
}
