<?php

namespace App\Libs\EloquentFilters;

readonly class FilterPayload
{
    public function __construct(
        public ?string $filterKey,
        public ?string $fieldName,
        public mixed $value,
        public ?string $matchMode,
        public array $params = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            filterKey: isset($data['filter_key']) && is_string($data['filter_key']) ? $data['filter_key'] : null,
            fieldName: isset($data['field_name']) && is_string($data['field_name']) ? $data['field_name'] : null,
            value: $data['value'] ?? null,
            matchMode: isset($data['matchMode']) && is_string($data['matchMode']) ? $data['matchMode'] : null,
            params: isset($data['params']) && is_array($data['params']) ? $data['params'] : [],
        );
    }

    public function matchModeEnum(?MatchMode $default = null): ?MatchMode
    {
        if ($this->matchMode === null) {
            return $default;
        }

        return MatchMode::tryFrom($this->matchMode) ?? $default;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }
}
