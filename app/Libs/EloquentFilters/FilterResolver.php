<?php

namespace App\Libs\EloquentFilters;

class FilterResolver
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  FilterDefinition[]  $allowedFilters
     */
    public function resolve(array $payload, array $allowedFilters): Filter
    {
        $filterPayload = FilterPayload::fromArray($payload);

        $definition = $this->findDefinition($filterPayload->filterKey, $allowedFilters);

        if (!$definition) {
            throw InvalidFilterException::unknownFilter($filterPayload->filterKey ?? '');
        }

        if (!empty($definition->allowedFields)) {
            if (!$filterPayload->fieldName || !in_array($filterPayload->fieldName, $definition->allowedFields, true)) {
                throw InvalidFilterException::fieldNotAllowed($filterPayload->fieldName ?? '', $filterPayload->filterKey ?? '');
            }
        }

        if ($filterPayload->matchMode !== null) {
            $matchMode = MatchMode::tryFrom($filterPayload->matchMode);

            if ($matchMode === null) {
                throw InvalidFilterException::unknownMatchMode($filterPayload->matchMode);
            }

            $supportedModes = ($definition->filterClass)::supportedMatchModes();

            if ($supportedModes !== null && !in_array($matchMode, $supportedModes, true)) {
                throw InvalidFilterException::unsupportedMatchMode($filterPayload->matchMode, $filterPayload->filterKey ?? '');
            }
        }

        return new ($definition->filterClass)($filterPayload);
    }

    /** @param FilterDefinition[] $allowedFilters */
    private function findDefinition(?string $filterKey, array $allowedFilters): ?FilterDefinition
    {
        if (!$filterKey) {
            return null;
        }

        foreach ($allowedFilters as $definition) {
            if ($definition->key() === $filterKey) {
                return $definition;
            }
        }

        return null;
    }
}
