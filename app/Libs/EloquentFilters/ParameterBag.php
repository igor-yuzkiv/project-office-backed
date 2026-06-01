<?php

namespace App\Libs\EloquentFilters;

readonly class ParameterBag
{
    public function __construct(private array $params = []) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->params);
    }

    public function boolean(string $key, bool $default = false): bool
    {
        return filter_var($this->get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public function matchMode(?MatchMode $default = null): ?MatchMode
    {
        $value = $this->get('matchMode');

        if ($value instanceof MatchMode) {
            return $value;
        }

        if (empty($value)) {
            return $default;
        }

        return MatchMode::tryFrom($value) ?? $default;
    }
}
