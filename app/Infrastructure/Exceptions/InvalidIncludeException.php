<?php

namespace App\Infrastructure\Exceptions;

use Illuminate\Http\JsonResponse;

class InvalidIncludeException extends \RuntimeException
{
    public static function forRelation(string $relation, array $allowed): self
    {
        $allowedList = empty($allowed) ? 'none' : implode(', ', $allowed);

        return new self("Include '{$relation}' is not allowed. Allowed includes: {$allowedList}.");
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
