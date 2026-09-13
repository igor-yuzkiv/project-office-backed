<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\Task\Queries\CountTasksPerTaskViewQuery;
use App\Domains\Task\ValueObjects\TaskViewCount;
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
    $project = ProjectModel::factory()->create();
    $list = TaskListModel::factory()->create(['project_id' => $project->id]);
    TaskModel::factory()->create(['project_id' => $project->id, 'task_list_id' => $list->id, 'updated_by' => $this->user->id]);

    $this->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonMissingPath('data.summary')
        ->assertJsonStructure([
            'data' => [
                'recent_tasks'      => [['id', 'key', 'name', 'status', 'updated_at', 'project', 'task_list', 'updated_by']],
                'recent_task_lists' => [['id', 'key', 'name', 'tasks_count', 'task_status_counts', 'project', 'updated_by']],
            ],
        ]);
});

it('answers an empty database with empty lists', function () {
    $response = $this->getJson('/api/dashboard')->assertOk();

    expect($response->json('data.recent_tasks'))->toBe([])
        ->and($response->json('data.recent_task_lists'))->toBe([]);
});

it('counts filtered tasks without paginating them', function () {
    dashboardTask(['status' => TaskStatus::Closed->value]);
    dashboardTask(['status' => TaskStatus::Backlog->value]);

    $counts = collect(app(CountTasksPerTaskViewQuery::class)->handle())
        ->mapWithKeys(fn (TaskViewCount $counted): array => [$counted->view->key => $counted->count]);

    expect($counts['all'])->toBe(2)
        ->and($counts['all_closed'])->toBe(1)
        ->and($counts['all_backlogged'])->toBe(1)
        ->and($counts['all_in_progress'])->toBe(0);
});

it('returns at most six recent tasks, newest first, each with its project, list and editor', function () {
    $list = TaskListModel::factory()->create(['project_id' => ProjectModel::factory()->create()->id]);
    $tasks = collect(range(1, 8))->map(fn (int $minutes) => tap(dashboardTask(['project_id' => $list->project_id, 'task_list_id' => $list->id, 'updated_by' => $this->user->id]), function (TaskModel $task) use ($minutes): void {
        $task->forceFill(['updated_at' => now()->addMinutes($minutes)])->saveQuietly();
    }));

    $response = $this->getJson('/api/dashboard')->assertOk();

    expect($response->json('data.recent_tasks'))->toHaveCount(6)
        ->and($response->json('data.recent_tasks.0.id'))->toBe($tasks->last()->id)
        ->and($response->json('data.recent_tasks.0.project.id'))->toBe($tasks->last()->project_id)
        ->and($response->json('data.recent_tasks.0.task_list.id'))->toBe($list->id)
        ->and($response->json('data.recent_tasks.0.updated_by.id'))->toBe($this->user->id);
});

it('returns at most three recent task lists, newest first, each with its task counts, project and editor', function () {
    $project = ProjectModel::factory()->create();

    // More lists than the limit, otherwise the cap is never exercised.
    $lists = collect(range(1, 5))->map(function (int $minutes) use ($project) {
        $list = TaskListModel::factory()->create(['project_id' => $project->id, 'updated_by' => $this->user->id]);
        $list->forceFill(['updated_at' => now()->addMinutes($minutes)])->saveQuietly();

        return $list;
    });

    TaskModel::factory()->count(2)->create([
        'project_id'   => $project->id,
        'task_list_id' => $lists->last()->id,
        'status'       => TaskStatus::Completed,
    ]);

    $response = $this->getJson('/api/dashboard')->assertOk();

    expect($response->json('data.recent_task_lists'))->toHaveCount(3)
        ->and($response->json('data.recent_task_lists.0.id'))->toBe($lists->last()->id)
        ->and($response->json('data.recent_task_lists.0.tasks_count'))->toBe(2)
        ->and($response->json('data.recent_task_lists.0.task_status_counts.completed'))->toBe(2)
        ->and($response->json('data.recent_task_lists.0.project.id'))->toBe($project->id)
        ->and($response->json('data.recent_task_lists.0.updated_by.id'))->toBe($this->user->id);
});

it('keeps the query count flat as the data grows', function () {
    $project = ProjectModel::factory()->create();
    TaskModel::factory()->count(2)->create(['project_id' => $project->id]);
    TaskListModel::factory()->count(2)->create(['project_id' => $project->id]);

    $small = countDashboardQueries(fn () => $this->getJson('/api/dashboard')->assertOk());

    TaskModel::factory()->count(20)->create(['project_id' => ProjectModel::factory()->create()->id]);
    TaskListModel::factory()->count(20)->create(['project_id' => ProjectModel::factory()->create()->id]);

    $large = countDashboardQueries(fn () => $this->getJson('/api/dashboard')->assertOk());

    // Row count is the axis that matters: without the eager loads every recent task would fetch its
    // own project, list and editor, and every recent list its own task counts.
    expect($large)->toBe($small)
        ->and($large)->toBeLessThanOrEqual(12);
});
