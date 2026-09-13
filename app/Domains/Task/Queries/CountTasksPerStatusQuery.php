<?php

namespace App\Domains\Task\Queries;

use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Counts tasks by status for each owner (project, task list) in one grouped query; every status
 * is present, missing ones as 0. A subclass names the task column that points at the owner.
 */
abstract class CountTasksPerStatusQuery
{
    abstract protected function ownerColumn(): string;

    /**
     * @param  array<string>  $ownerIds
     * @return array<string, array<string, int>>
     */
    public function handle(array $ownerIds): array
    {
        $ownerIds = array_values(array_unique($ownerIds));
        if ($ownerIds === []) {
            return [];
        }

        $column = $this->ownerColumn();
        $empty = array_fill_keys(array_column(TaskStatus::cases(), 'value'), 0);
        $counts = array_fill_keys($ownerIds, $empty);

        $rows = TaskModel::query()
            ->select($column, 'status', DB::raw('count(*) as aggregate'))
            ->whereIn($column, $ownerIds)
            ->groupBy($column, 'status')
            ->toBase()
            ->get();

        foreach ($rows as $row) {
            $counts[$row->{$column}][$row->status] = (int) $row->aggregate;
        }

        return $counts;
    }

    /**
     * Sets `task_status_counts` on every given owner from one query, so a page of owners never
     * counts per row.
     *
     * @param  iterable<Model>  $owners
     */
    public function attach(iterable $owners): void
    {
        $owners = collect($owners);
        $counts = $this->handle($owners->map(fn (Model $owner) => $owner->getKey())->all());

        foreach ($owners as $owner) {
            $owner->setAttribute('task_status_counts', $counts[$owner->getKey()]);
        }
    }
}
