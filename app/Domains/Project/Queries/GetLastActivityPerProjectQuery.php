<?php

namespace App\Domains\Project\Queries;

use App\Domains\Project\Models\ProjectModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Database\Eloquent\Collection;

class GetLastActivityPerProjectQuery
{
    /**
     * Finds the newest audit record of each project in one grouped query, plus one for their authors.
     *
     * @param  array<string>  $projectIds
     * @return Collection<string, AuditRecordModel> keyed by project id; projects without events are absent
     */
    public function handle(array $projectIds): Collection
    {
        $projectIds = array_values(array_unique($projectIds));
        if ($projectIds === []) {
            return new Collection;
        }

        // DISTINCT ON keeps the first row per project of the ordered set, so the
        // newest event wins without a per-project subquery.
        return AuditRecordModel::query()
            ->selectRaw('distinct on (project_id) audit_records.*')
            ->whereIn('project_id', $projectIds)
            ->orderBy('project_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->with('createdBy')
            ->get()
            ->keyBy('project_id');
    }

    /**
     * Sets `last_activity` on every given project without a query per row: the newest audit record or null.
     *
     * @param  iterable<ProjectModel>  $projects
     */
    public function attach(iterable $projects): void
    {
        $projects = collect($projects);
        $records = $this->handle($projects->map(fn (ProjectModel $project) => $project->id)->all());

        foreach ($projects as $project) {
            $project->setAttribute('last_activity', $records->get($project->id));
        }
    }
}
