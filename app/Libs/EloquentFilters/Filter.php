<?php

namespace App\Libs\EloquentFilters;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    public function __construct(protected ParameterBag $params) {}

    abstract public static function key(): string;

    abstract public function apply(Builder $query): Builder;

    /** @return MatchMode[]|null null means matchMode is not used by this filter */
    public static function supportedMatchModes(): ?array
    {
        return null;
    }

    protected function matchMode(?MatchMode $default = null): ?MatchMode
    {
        return $this->params->matchMode($default);
    }
}
