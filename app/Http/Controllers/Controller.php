<?php

namespace App\Http\Controllers;

use App\Infrastructure\DTO\PaginationParams;
use App\Infrastructure\DTO\SortParams;

abstract class Controller
{
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
}
