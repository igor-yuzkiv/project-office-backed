<?php

namespace App\Domains\Project\Queries;

use App\Domains\Project\Models\ProjectModel;
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

    /**
     * Sets `task_status_counts` on every given project from one query, so a page of projects
     * never counts per row.
     *
     * @param  iterable<ProjectModel>  $projects
     */
    public function attach(iterable $projects): void
    {
        $projects = collect($projects);
        $counts = $this->handle($projects->map(fn (ProjectModel $project) => $project->id)->all());

        foreach ($projects as $project) {
            $project->setAttribute('task_status_counts', $counts[$project->id]);
        }
    }
}
