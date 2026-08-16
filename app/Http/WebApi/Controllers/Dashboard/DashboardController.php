<?php

namespace App\Http\WebApi\Controllers\Dashboard;

use App\Domains\Dashboard\Services\RecentTaskListsService;
use App\Domains\Dashboard\Services\RecentTasksService;
use App\Domains\Dashboard\Services\TaskViewCountsService;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Http\Shared\Resources\TaskLists\TaskListResource;
use App\Http\Shared\Resources\Tasks\TaskOverviewResource;
use App\Http\WebApi\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;

class DashboardController extends ResourceController
{
    public function __construct(
        private readonly TaskViewCountsService $taskViewCounts,
        private readonly RecentTasksService $recentTasks,
        private readonly RecentTaskListsService $recentTaskLists,
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
                    'task_views'       => $this->taskViewCounts->get(),
                    'projects_count'   => ProjectModel::query()->count(),
                    'task_lists_count' => TaskListModel::query()->count(),
                ],
                // The dashboard shows the latest activity by definition, so both lists are fixed to
                // updated_at desc. sort_by / sort_order / per_page are deliberately not read here.
                'recent_tasks'      => TaskOverviewResource::collection($this->recentTasks->get()),
                'recent_task_lists' => TaskListResource::collection($this->recentTaskLists->get()),
            ],
        ]);
    }
}
