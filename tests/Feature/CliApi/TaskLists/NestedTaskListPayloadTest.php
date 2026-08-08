<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The task list nested inside agent-facing payloads is the compact overview form. Extending
 * TaskListResource must not widen what the CLI API returns, so the nested shape is pinned here.
 */
beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create(['prefix' => 'MTM']);
    $this->taskList = TaskListModel::factory()->create([
        'project_id'  => $this->project->id,
        'key'         => 'MTM-TL-1',
        'name'        => 'Backlog',
        'status'      => TaskListStatus::Open->value,
        'description' => '# A long description that must not reach the agent payload',
    ]);
});

it('nests the compact task list form in the CLI task payload', function () {
    $task = TaskModel::factory()->create([
        'project_id'   => $this->project->id,
        'task_list_id' => $this->taskList->id,
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}");

    $response->assertOk()
        ->assertJsonPath('data.task_list.id', $this->taskList->id)
        ->assertJsonPath('data.task_list.key', 'MTM-TL-1')
        ->assertJsonPath('data.task_list.status', 'open');

    expect(array_keys($response->json('data.task_list')))
        ->toBe(['id', 'project_id', 'key', 'name', 'status', 'created_at', 'updated_at']);
});

it('nests the compact task list form in the CLI task list endpoint', function () {
    TaskModel::factory()->create([
        'project_id'   => $this->project->id,
        'task_list_id' => $this->taskList->id,
        'status'       => TaskStatus::Open->value,
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/list?include=taskList");

    $response->assertOk();

    expect(array_keys($response->json('data.0.task_list')))
        ->toBe(['id', 'project_id', 'key', 'name', 'status', 'created_at', 'updated_at']);
});

// The CLI project endpoint does not allow the taskLists include, so no task list reaches that
// payload at all. Pinned so the slot is not opened up unnoticed.
it('does not nest task lists in the CLI project payload', function () {
    $response = $this->getJson("/api/cli/projects/{$this->project->id}");

    $response->assertOk()
        ->assertJsonMissingPath('data.task_lists');

    $this->getJson("/api/cli/projects/{$this->project->id}?include=taskLists")
        ->assertStatus(422);
});
