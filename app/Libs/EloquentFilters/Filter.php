<?php

namespace App\Libs\EloquentFilters;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

abstract class Filter
{
    public function __construct(protected FilterPayload $payload) {}

    abstract public static function key(): string;

    abstract public function apply(Builder|ScoutBuilder $query): Builder|ScoutBuilder;

    /** @return MatchMode[]|null null means matchMode is not used by this filter */
    public static function supportedMatchModes(): ?array
    {
        return null;
    }

    protected function matchMode(?MatchMode $default = null): ?MatchMode
    {
        return $this->payload->matchModeEnum($default);
    }
}
