<?php

namespace App\Domains\Task\Services;

use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\ValueObjects\TaskView;
use App\Libs\EloquentFilters\FilterPayload;
use App\Libs\EloquentFilters\Filters\TextFilter;
use App\Libs\EloquentFilters\MatchMode;

class TaskViewRegistry
{
    /**
     * @return TaskView[]
     */
    public static function all(): array
    {
        return [
            new TaskView('all', 'All', []),
            new TaskView('all_open', 'All Open', [
                self::statusFilter([
                    TaskStatus::Open,
                    TaskStatus::ReadyForDevelopment,
                    TaskStatus::InProgress,
                    TaskStatus::ReadyToTest,
                    TaskStatus::Completed,
                ]),
            ]),
            new TaskView('all_in_progress', 'All In Progress', [
                self::statusFilter([
                    TaskStatus::ReadyForDevelopment,
                    TaskStatus::InProgress,
                    TaskStatus::ReadyToTest,
                ]),
            ]),
            new TaskView('all_closed', 'All Closed', [
                self::statusFilter([
                    TaskStatus::Closed,
                ]),
            ]),
            new TaskView('all_backlogged', 'All Backlogged', [
                self::statusFilter([
                    TaskStatus::Backlog,
                ]),
            ]),
        ];
    }

    /**
     * @param  TaskStatus[]  $statuses
     */
    private static function statusFilter(array $statuses): FilterPayload
    {
        return new FilterPayload(
            filterKey: TextFilter::key(),
            fieldName: 'status',
            value: array_map(static fn (TaskStatus $status): string => $status->value, $statuses),
            matchMode: MatchMode::IN->value,
            params: [],
        );
    }
}
