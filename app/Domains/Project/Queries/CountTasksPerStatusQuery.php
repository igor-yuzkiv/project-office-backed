<?php

namespace App\Domains\Project\Queries;

use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Support\Facades\DB;

class CountTasksPerStatusQuery
{
    /**
     * Counts tasks by status for each project in one grouped query; every status is present, missing ones as 0.
     *
     * @param  array<string>  $projectIds
     * @return array<string, array<string, int>>
     */
    public function handle(array $projectIds): array
    {
        $projectIds = array_values(array_unique($projectIds));
        if ($projectIds === []) {
            return [];
        }

        $empty = array_fill_keys(array_column(TaskStatus::cases(), 'value'), 0);
        $counts = array_fill_keys($projectIds, $empty);

        $rows = TaskModel::query()
            ->select('project_id', 'status', DB::raw('count(*) as aggregate'))
            ->whereIn('project_id', $projectIds)
            ->groupBy('project_id', 'status')
            ->toBase()
            ->get();

        foreach ($rows as $row) {
            $counts[$row->project_id][$row->status] = (int) $row->aggregate;
        }

        return $counts;
    }
}
