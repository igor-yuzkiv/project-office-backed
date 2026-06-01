<?php

namespace App\Libs\EloquentFilters;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class InvalidFilterException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 400);
    }
}
