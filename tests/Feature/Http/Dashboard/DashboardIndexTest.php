<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\Task\Queries\CountTasksPerTaskViewQuery;
use App\Domains\Task\Services\TaskViewRegistry;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
});

function countDashboardQueries(Closure $callback): int
{
    $count = 0;
    DB::listen(function () use (&$count): void {
        $count++;
    });

    $callback();

    return $count;
}

function dashboardTask(array $attributes = []): TaskModel
{
    return TaskModel::factory()->create([
        'project_id' => ProjectModel::factory()->create()->id,
        ...$attributes,
    ]);
}

it('rejects an unauthenticated request', function () {
    auth()->logout();

    $this->getJson('/api/dashboard')->assertUnauthorized();
});

it('returns the full dashboard envelope', function () {
    dashboardTask();
    TaskListModel::factory()->create(['project_id' => ProjectModel::factory()->create()->id]);

    $this->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'summary' => [
                    'task_views' => [['key', 'label', 'count']],
                    'projects_count',
                    'task_lists_count',
                ],
                'recent_tasks'      => [['id', 'key', 'name', 'status', 'updated_at', 'project']],
                'recent_task_lists' => [['id', 'key', 'name', 'tasks_count']],
            ],
        ]);
});

it('answers an empty database with zeros and empty lists', function () {
    $response = $this->getJson('/api/dashboard')->assertOk();

    expect($response->json('data.summary.projects_count'))->toBe(0)
        ->and($response->json('data.summary.task_lists_count'))->toBe(0)
        ->and($response->json('data.recent_tasks'))->toBe([])
        ->and($response->json('data.recent_task_lists'))->toBe([])
        ->and(collect($response->json('data.summary.task_views'))->pluck('count')->all())
        ->each->toBe(0);
});

it('takes the task views straight from the registry', function () {
    $views = TaskViewRegistry::all();

    $response = $this->getJson('/api/dashboard')->assertOk();

    expect($response->json('data.summary.task_views'))->toHaveCount(count($views));

    foreach ($views as $index => $view) {
        expect($response->json("data.summary.task_views.$index.key"))->toBe($view->key)
            ->and($response->json("data.summary.task_views.$index.label"))->toBe($view->label);
    }
});

it('counts each view the same way the task search does', function () {
    dashboardTask(['status' => TaskStatus::Open->value]);
    dashboardTask(['status' => TaskStatus::InProgress->value]);
    dashboardTask(['status' => TaskStatus::Closed->value]);
    dashboardTask(['status' => TaskStatus::Backlog->value]);

    $dashboard = $this->getJson('/api/dashboard')->assertOk();
    // The filters travel to the search endpoint exactly as the Tasks page sends them.
    $views = $this->getJson('/api/task-views')->assertOk()->json('data');

    foreach ($views as $index => $view) {
        $searched = $this->postJson('/api/tasks/search', ['filters' => $view['filters']])->assertOk();

        expect($dashboard->json("data.summary.task_views.$index.count"))
            ->toBe($searched->json('meta.total'));
    }
});

it('counts filtered tasks without paginating them', function () {
    dashboardTask(['status' => TaskStatus::Closed->value]);
    dashboardTask(['status' => TaskStatus::Backlog->value]);

    $counts = collect(app(CountTasksPerTaskViewQuery::class)->handle())->keyBy('key');

    expect($counts['all']['count'])->toBe(2)
        ->and($counts['all_closed']['count'])->toBe(1)
        ->and($counts['all_backlogged']['count'])->toBe(1)
        ->and($counts['all_in_progress']['count'])->toBe(0);
});

it('returns at most eight recent tasks, newest first, each with its project', function () {
    $tasks = collect(range(1, 10))->map(fn (int $minutes) => tap(dashboardTask(), function (TaskModel $task) use ($minutes): void {
        $task->forceFill(['updated_at' => now()->addMinutes($minutes)])->saveQuietly();
    }));

    $response = $this->getJson('/api/dashboard')->assertOk();

    expect($response->json('data.recent_tasks'))->toHaveCount(8)
        ->and($response->json('data.recent_tasks.0.id'))->toBe($tasks->last()->id)
        ->and($response->json('data.recent_tasks.0.project.id'))->toBe($tasks->last()->project_id);
});

it('returns at most four recent task lists, newest first, each with its task count', function () {
    $project = ProjectModel::factory()->create();

    $lists = collect(range(1, 6))->map(function (int $minutes) use ($project) {
        $list = TaskListModel::factory()->create(['project_id' => $project->id]);
        $list->forceFill(['updated_at' => now()->addMinutes($minutes)])->saveQuietly();

        return $list;
    });

    TaskModel::factory()->count(2)->create([
        'project_id'   => $project->id,
        'task_list_id' => $lists->last()->id,
    ]);

    $response = $this->getJson('/api/dashboard')->assertOk();

    expect($response->json('data.recent_task_lists'))->toHaveCount(4)
        ->and($response->json('data.recent_task_lists.0.id'))->toBe($lists->last()->id)
        ->and($response->json('data.recent_task_lists.0.tasks_count'))->toBe(2);
});

it('keeps the query count flat as the data grows', function () {
    $project = ProjectModel::factory()->create();
    TaskModel::factory()->count(2)->create(['project_id' => $project->id]);
    TaskListModel::factory()->count(2)->create(['project_id' => $project->id]);

    $small = countDashboardQueries(fn () => $this->getJson('/api/dashboard')->assertOk());

    TaskModel::factory()->count(20)->create(['project_id' => ProjectModel::factory()->create()->id]);
    TaskListModel::factory()->count(20)->create(['project_id' => ProjectModel::factory()->create()->id]);

    $large = countDashboardQueries(fn () => $this->getJson('/api/dashboard')->assertOk());

    // Row count is the axis that matters: without the eager load every recent task would fetch its
    // own project, and every recent list its own task count.
    expect($large)->toBe($small)
        ->and($large)->toBeLessThanOrEqual(12);
});
