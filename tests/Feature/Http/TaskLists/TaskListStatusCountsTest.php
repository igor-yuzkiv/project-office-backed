<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

const ALL_ZERO_COUNTS = [
    'backlog'               => 0,
    'open'                  => 0,
    'ready_for_development' => 0,
    'in_progress'           => 0,
    'ready_to_test'         => 0,
    'completed'             => 0,
    'closed'                => 0,
    'declined'              => 0,
];

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
});

function taskListWithTasks(ProjectModel $project, array $statusCounts): TaskListModel
{
    $taskList = TaskListModel::factory()->create(['project_id' => $project->id]);
    foreach ($statusCounts as $status => $count) {
        TaskModel::factory()->count($count)->create([
            'project_id'   => $project->id,
            'task_list_id' => $taskList->id,
            'status'       => $status,
        ]);
    }

    return $taskList;
}

it('shows counts for every status on a single task list', function () {
    $taskList = taskListWithTasks($this->project, ['open' => 3, 'in_progress' => 1, 'completed' => 2, 'declined' => 1]);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open]);

    $this->getJson("/api/task-lists/{$taskList->id}")
        ->assertOk()
        ->assertJsonPath('data.task_status_counts', [
            ...ALL_ZERO_COUNTS,
            'open'        => 3,
            'in_progress' => 1,
            'completed'   => 2,
            'declined'    => 1,
        ]);
});

it('returns zeros for a task list without tasks in index, search and show', function () {
    $taskList = TaskListModel::factory()->create(['project_id' => $this->project->id]);

    $this->getJson('/api/task-lists')
        ->assertOk()
        ->assertJsonPath('data.0.task_status_counts', ALL_ZERO_COUNTS);
    $this->postJson('/api/task-lists/search', ['query' => ''])
        ->assertOk()
        ->assertJsonPath('data.0.task_status_counts', ALL_ZERO_COUNTS);
    $this->getJson("/api/task-lists/{$taskList->id}")
        ->assertOk()
        ->assertJsonPath('data.task_status_counts', ALL_ZERO_COUNTS);
});

it('counts each list in index separately with a single query', function () {
    $first = taskListWithTasks($this->project, ['backlog' => 2]);
    $second = taskListWithTasks($this->project, ['closed' => 1]);
    $third = taskListWithTasks($this->project, []);

    DB::enableQueryLog();
    $response = $this->getJson('/api/task-lists?per_page=10')->assertOk()->assertJsonCount(3, 'data');
    $countQueries = array_filter(DB::getQueryLog(), fn (array $q) => str_contains($q['query'], 'group by "task_list_id", "status"'));
    DB::disableQueryLog();

    expect($countQueries)->toHaveCount(1);

    $byId = collect($response->json('data'))->keyBy('id');
    expect($byId[$first->id]['task_status_counts']['backlog'])->toBe(2)
        ->and($byId[$second->id]['task_status_counts']['closed'])->toBe(1)
        ->and($byId[$third->id]['task_status_counts'])->toBe(ALL_ZERO_COUNTS);
});

it('carries counts in project search results', function () {
    $taskList = taskListWithTasks($this->project, ['backlog' => 4]);

    $this->postJson('/api/projects/search?include=taskLists', ['query' => ''])
        ->assertOk()
        ->assertJsonPath('data.0.task_lists.0.id', $taskList->id)
        ->assertJsonPath('data.0.task_lists.0.task_status_counts.backlog', 4);
});

it('carries counts in search results', function () {
    $taskList = taskListWithTasks($this->project, ['ready_to_test' => 2]);

    $this->postJson('/api/task-lists/search', ['query' => ''])
        ->assertOk()
        ->assertJsonPath('data.0.id', $taskList->id)
        ->assertJsonPath('data.0.task_status_counts.ready_to_test', 2);
});

it('carries counts on the list returned by store and update', function () {
    $created = $this->postJson('/api/task-lists', [
        'project_id' => $this->project->id,
        'name'       => 'Release plan',
        'status'     => 'open',
    ])->assertCreated()->assertJsonPath('data.task_status_counts', ALL_ZERO_COUNTS);

    $taskList = TaskListModel::findOrFail($created->json('data.id'));
    TaskModel::factory()->create(['project_id' => $this->project->id, 'task_list_id' => $taskList->id, 'status' => TaskStatus::InProgress]);

    $this->putJson("/api/task-lists/{$taskList->id}", ['name' => 'Release plan', 'status' => 'in_progress'])
        ->assertOk()
        ->assertJsonPath('data.task_status_counts.in_progress', 1);
});

it('counts nested task lists of a project in show and index with one query per page', function () {
    $first = taskListWithTasks($this->project, ['open' => 1]);
    $second = taskListWithTasks($this->project, ['completed' => 2]);
    $otherProject = ProjectModel::factory()->create();
    $third = taskListWithTasks($otherProject, ['declined' => 1]);

    $show = $this->getJson("/api/projects/{$this->project->id}?include=taskLists")->assertOk();
    $shown = collect($show->json('data.task_lists'))->keyBy('id');
    expect($shown[$first->id]['task_status_counts'])->toBe([...ALL_ZERO_COUNTS, 'open' => 1])
        ->and($shown[$second->id]['task_status_counts'])->toBe([...ALL_ZERO_COUNTS, 'completed' => 2]);

    DB::enableQueryLog();
    $index = $this->getJson('/api/projects?include=taskLists&per_page=10')->assertOk()->assertJsonCount(2, 'data');
    $countQueries = array_filter(DB::getQueryLog(), fn (array $q) => str_contains($q['query'], 'group by "task_list_id", "status"'));
    DB::disableQueryLog();

    expect($countQueries)->toHaveCount(1);
    $lists = collect($index->json('data'))->flatMap(fn (array $project) => $project['task_lists'])->keyBy('id');
    expect($lists[$first->id]['task_status_counts']['open'])->toBe(1)
        ->and($lists[$second->id]['task_status_counts']['completed'])->toBe(2)
        ->and($lists[$third->id]['task_status_counts']['declined'])->toBe(1);
});

it('leaves nested task lists without counts when they are not included', function () {
    TaskListModel::factory()->create(['project_id' => $this->project->id]);

    $this->getJson("/api/projects/{$this->project->id}")
        ->assertOk()
        ->assertJsonMissingPath('data.task_lists');
});

it('carries counts on the recent task lists of the dashboard', function () {
    $taskList = taskListWithTasks($this->project, ['in_progress' => 2, 'closed' => 1]);

    $this->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('data.recent_task_lists.0.id', $taskList->id)
        ->assertJsonPath('data.recent_task_lists.0.task_status_counts', [...ALL_ZERO_COUNTS, 'in_progress' => 2, 'closed' => 1]);
});

it('keeps the counts out of the CLI task list payloads', function () {
    $taskList = taskListWithTasks($this->project, ['open' => 1]);

    $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/list")
        ->assertOk()
        ->assertJsonMissingPath('data.0.task_status_counts');
    $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/{$taskList->id}")
        ->assertOk()
        ->assertJsonMissingPath('data.task_status_counts');
});
