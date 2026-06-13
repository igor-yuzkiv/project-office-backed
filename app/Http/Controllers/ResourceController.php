<?php

namespace App\Http\Controllers;

use App\Infrastructure\DTO\PaginationParams;
use App\Infrastructure\DTO\SortParams;
use App\Infrastructure\Exceptions\InvalidIncludeException;

abstract class ResourceController
{
    /** @return string[] Eloquent relation names that clients may request */
    abstract protected function getAllowedIncludes(): array;

    protected function getPaginationParams(): PaginationParams
    {
        return new PaginationParams(
            page: request()->input('page', 1),
            perPage: request()->input('per_page', 10),
        );
    }

    protected function getSortParams(): SortParams
    {
        return new SortParams(
            field: request()->input('sort_by', 'updated_at'),
            direction: request()->input('sort_order', 'desc'),
        );
    }

    /**
     * Merges always-required relations with client-requested ones.
     * Throws if any requested relation is not in getAllowedIncludes().
     *
     * @param  string[]  $required  Relations always loaded for this action
     * @param  string[]  $requested  Relations requested by the client
     * @return string[]
     */
    protected function resolveIncludes(array $required, array $requested): array
    {
        $allowed = $this->getAllowedIncludes();

        foreach ($requested as $relation) {
            if (!in_array($relation, $allowed, true)) {
                throw InvalidIncludeException::forRelation($relation, $allowed);
            }
        }

        return array_values(array_unique([...$required, ...$requested]));
    }

    /**
     * Reads the include parameter from the current request.
     * Accepts a comma-separated string (GET) or an array (POST body).
     *
     * @return string[]
     */
    protected function parseRequestedIncludes(): array
    {
        $raw = request()->input('include', []);

        if (is_string($raw)) {
            return array_values(array_filter(array_map('trim', explode(',', $raw))));
        }

        return array_values(array_map('trim', (array) $raw));
    }
}
