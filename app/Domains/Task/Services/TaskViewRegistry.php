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
            new TaskView('all_open', 'All open', [
                self::statusFilter([
                    TaskStatus::Open,
                    TaskStatus::ReadyForDevelopment,
                    TaskStatus::InProgress,
                ]),
            ]),
            new TaskView('all_in_progress', 'All in progress', [
                self::statusFilter([
                    TaskStatus::ReadyForDevelopment,
                    TaskStatus::InProgress,
                    TaskStatus::ReadyToTest,
                    TaskStatus::Completed,
                ]),
            ]),
            new TaskView('all_closed', 'All closed', [
                self::statusFilter([
                    TaskStatus::Closed,
                    TaskStatus::Declined,
                ]),
            ]),
            new TaskView('all_backlogged', 'All backlogged', [
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
