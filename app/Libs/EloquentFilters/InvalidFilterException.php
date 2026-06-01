<?php

namespace App\Libs\EloquentFilters;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class InvalidFilterException extends RuntimeException
{
    private function __construct(
        string $message,
        private readonly array $context = [],
    ) {
        parent::__construct($message);
    }

    public static function unknownFilter(string $filter): self
    {
        return new self(
            "Unknown filter: \"{$filter}\".",
            ['filter' => $filter],
        );
    }

    public static function fieldNotAllowed(string $field, string $filter): self
    {
        return new self(
            "Field \"{$field}\" is not allowed for filter \"{$filter}\".",
            ['filter' => $filter, 'field' => $field],
        );
    }

    public static function unknownMatchMode(string $matchMode): self
    {
        return new self(
            "Unknown match mode: \"{$matchMode}\".",
            ['matchMode' => $matchMode],
        );
    }

    public static function unsupportedMatchMode(string $matchMode, string $filter): self
    {
        return new self(
            "Match mode \"{$matchMode}\" is not supported by filter \"{$filter}\".",
            ['filter' => $filter, 'matchMode' => $matchMode],
        );
    }

    public function context(): array
    {
        return $this->context;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'context' => $this->context,
        ], 400);
    }
}
