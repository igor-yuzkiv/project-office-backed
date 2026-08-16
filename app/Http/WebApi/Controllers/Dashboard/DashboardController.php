<?php

namespace App\Http\WebApi\Controllers\Dashboard;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Queries\CountTasksPerTaskViewQuery;
use App\Domains\Task\Queries\GetRecentTasksQuery;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\TaskList\Queries\GetRecentTaskListsQuery;
use App\Http\Shared\Resources\TaskLists\TaskListResource;
use App\Http\Shared\Resources\Tasks\TaskOverviewResource;
use App\Http\WebApi\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;

class DashboardController extends ResourceController
{
    private const RECENT_TASKS_LIMIT = 8;

    private const RECENT_TASK_LISTS_LIMIT = 6;

    public function __construct(
        private readonly CountTasksPerTaskViewQuery $countTasksPerTaskView,
        private readonly GetRecentTasksQuery $getRecentTasks,
        private readonly GetRecentTaskListsQuery $getRecentTaskLists,
    ) {}

    protected function getAllowedIncludes(): array
    {
        return [];
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'summary' => [
                    'task_views'       => $this->countTasksPerTaskView->handle(),
                    'projects_count'   => ProjectModel::query()->count(),
                    'task_lists_count' => TaskListModel::query()->count(),
                ],
                // The dashboard shows the latest activity by definition, so both lists are fixed to
                // updated_at desc. sort_by / sort_order / per_page are deliberately not read here.
                'recent_tasks'      => TaskOverviewResource::collection($this->getRecentTasks->handle(self::RECENT_TASKS_LIMIT)),
                'recent_task_lists' => TaskListResource::collection($this->getRecentTaskLists->handle(self::RECENT_TASK_LISTS_LIMIT)),
            ],
        ]);
    }
}
