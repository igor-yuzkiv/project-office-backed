<?php

namespace App\Domains\Project\Queries;

use App\Domains\Project\Models\ProjectModel;
use App\Infrastructure\DTO\PaginationParams;
use App\Infrastructure\DTO\SortParams;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SearchProjectsQuery
{
    public function __construct(
        private readonly string $query,
        private readonly array $filters,
        private readonly SortParams $sort,
        private readonly PaginationParams $pagination,
    ) {}

    public function run(): LengthAwarePaginator
    {
        return ProjectModel::search($this->query)
            ->orderBy($this->sort->field, $this->sort->direction)
            ->query(function (Builder $q): Builder {
                /** @var Builder<ProjectModel> $q */
                return $q->with(['createdBy', 'updatedBy'])->filter($this->filters);
            })
            ->paginate($this->pagination->perPage, 'page', $this->pagination->page);
    }
}
