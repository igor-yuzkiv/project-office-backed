<?php

namespace App\Libs\EloquentFilters;

class FilterResolver
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, array{0: class-string<Filter>, 1: array{allowed_fields: string[]}}>  $allowedFilters
     */
    public function resolve(array $payload, array $allowedFilters): Filter
    {
        $filterKey = $payload['filter'] ?? null;
        $field = $payload['field'] ?? null;
        $matchModeValue = $payload['matchMode'] ?? null;
        $value = $payload['value'] ?? null;
        $params = isset($payload['params']) && is_array($payload['params']) ? $payload['params'] : [];

        if (!$filterKey || !array_key_exists($filterKey, $allowedFilters)) {
            throw new InvalidFilterException("Unknown filter: \"{$filterKey}\".");
        }

        [$filterClass, $options] = $allowedFilters[$filterKey];
        $allowedFields = $options['allowed_fields'];

        if (!$field || !in_array($field, $allowedFields, true)) {
            throw new InvalidFilterException("Field \"{$field}\" is not allowed for filter \"{$filterKey}\".");
        }

        if ($matchModeValue !== null) {
            $matchMode = MatchMode::tryFrom($matchModeValue);

            if ($matchMode === null) {
                throw new InvalidFilterException("Unknown match mode: \"{$matchModeValue}\".");
            }

            $supportedModes = $filterClass::supportedMatchModes();

            if ($supportedModes !== null && !in_array($matchMode, $supportedModes, true)) {
                throw new InvalidFilterException("Match mode \"{$matchModeValue}\" is not supported by filter \"{$filterKey}\".");
            }
        }

        $parameterBag = new ParameterBag(array_merge($params, [
            'field'     => $field,
            'value'     => $value,
            'matchMode' => $matchModeValue,
        ]));

        return new $filterClass($parameterBag);
    }
}
