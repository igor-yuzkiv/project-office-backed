<?php

namespace App\Http\WebApi\Controllers\Dashboard;

use App\Domains\Task\Queries\GetRecentTasksQuery;
use App\Domains\TaskList\Queries\CountTasksPerStatusQuery;
use App\Domains\TaskList\Queries\GetRecentTaskListsQuery;
use App\Http\Shared\Resources\TaskLists\TaskListResource;
use App\Http\Shared\Resources\Tasks\TaskOverviewResource;
use App\Http\WebApi\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;

class DashboardController extends ResourceController
{
    private const RECENT_TASKS_LIMIT = 6;

    private const RECENT_TASK_LISTS_LIMIT = 3;

    public function __construct(
        private readonly GetRecentTasksQuery $getRecentTasks,
        private readonly GetRecentTaskListsQuery $getRecentTaskLists,
        private readonly CountTasksPerStatusQuery $countTasksPerStatus,
    ) {}

    protected function getAllowedIncludes(): array
    {
        return [];
    }

    public function index(): JsonResponse
    {
        $recentTaskLists = $this->getRecentTaskLists->handle(self::RECENT_TASK_LISTS_LIMIT);
        $this->countTasksPerStatus->attach($recentTaskLists);

        return response()->json([
            'data' => [
                // The dashboard shows the latest activity by definition, so both lists are fixed to
                // updated_at desc. sort_by / sort_order / per_page are deliberately not read here.
                'recent_tasks'      => TaskOverviewResource::collection($this->getRecentTasks->handle(self::RECENT_TASKS_LIMIT)),
                'recent_task_lists' => TaskListResource::collection($recentTaskLists),
            ],
        ]);
    }
}
