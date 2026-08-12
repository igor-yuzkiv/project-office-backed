<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
    $this->taskList = TaskListModel::factory()->create(['project_id' => $this->project->id]);
});

function taskInList(TaskListModel $list, array $attributes = []): TaskModel
{
    return TaskModel::factory()->create([
        'project_id'   => $list->project_id,
        'task_list_id' => $list->id,
        ...$attributes,
    ]);
}

it('returns the tasks of the list ordered by name', function () {
    foreach (['10. Documentation', '2. Backend API', '1. Data model'] as $name) {
        taskInList($this->taskList, ['name' => $name]);
    }

    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/tasks");

    $response->assertOk()
        ->assertJsonPath('data.0.name', '1. Data model')
        ->assertJsonPath('data.1.name', '2. Backend API')
        ->assertJsonPath('data.2.name', '10. Documentation');
});

it('orders tasks sharing a name by sequence number', function () {
    $second = taskInList($this->taskList, ['name' => 'Same', 'sequence_number' => 20]);
    $first = taskInList($this->taskList, ['name' => 'Same', 'sequence_number' => 10]);

    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/tasks");

    $response->assertOk()
        ->assertJsonPath('data.0.id', $first->id)
        ->assertJsonPath('data.1.id', $second->id);
});

it('paginates and respects page and per_page', function () {
    foreach (['1. A', '2. B', '3. C'] as $name) {
        taskInList($this->taskList, ['name' => $name]);
    }

    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/tasks?per_page=2&page=2");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', '3. C')
        ->assertJsonPath('meta.total', 3);
});

it('includes tasks in every status, backlog and closed among them', function () {
    taskInList($this->taskList, ['name' => '1. Backlogged', 'status' => TaskStatus::Backlog->value]);
    taskInList($this->taskList, ['name' => '2. Closed', 'status' => TaskStatus::Closed->value]);

    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/tasks");

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.status', TaskStatus::Backlog->value)
        ->assertJsonPath('data.1.status', TaskStatus::Closed->value);
});

it('leaves out tasks of other lists and tasks belonging to no list', function () {
    $other = TaskListModel::factory()->create(['project_id' => $this->project->id]);
    $mine = taskInList($this->taskList, ['name' => 'Mine']);
    taskInList($other, ['name' => 'Theirs']);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'task_list_id' => null, 'name' => 'Loose']);

    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/tasks");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $mine->id);
});

it('returns an empty collection for a list without tasks', function () {
    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/tasks");

    $response->assertOk()->assertJsonCount(0, 'data');
});

it('carries the fields the sidebar renders', function () {
    $task = taskInList($this->taskList, ['name' => '1. Data model', 'status' => TaskStatus::InProgress->value]);

    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/tasks");

    $response->assertOk()
        ->assertJsonPath('data.0.id', $task->id)
        ->assertJsonPath('data.0.key', $task->key)
        ->assertJsonPath('data.0.name', '1. Data model')
        ->assertJsonPath('data.0.status', TaskStatus::InProgress->value)
        ->assertJsonPath('data.0.task_list_id', $this->taskList->id);
});

it('rejects an unauthenticated request', function () {
    auth()->forgetGuards();

    $this->getJson("/api/task-lists/{$this->taskList->id}/tasks")->assertUnauthorized();
});

it('returns 404 for a task list that does not exist', function () {
    $this->getJson('/api/task-lists/01k000000000000000000000/tasks')->assertNotFound();
});
