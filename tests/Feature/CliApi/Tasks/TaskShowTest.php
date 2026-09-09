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
});

it('shows a task by id', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $task->id);
});

it('shows a task by key', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$task->key}");

    $response->assertOk()
        ->assertJsonPath('data.id', $task->id);
});

it('returns 404 for a task belonging to another project', function () {
    $otherProject = ProjectModel::factory()->create();
    $task = TaskModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}");

    $response->assertNotFound();
});

it('shows the tasks of the task list in plan order, every status included', function () {
    $taskList = TaskListModel::factory()->create(['project_id' => $this->project->id]);
    $tasks = [];
    foreach ([
        ['name' => '10. Ship it', 'sequence_number' => 1, 'status' => TaskStatus::Closed],
        ['name' => '1. Data model', 'sequence_number' => 3, 'status' => TaskStatus::Backlog],
        ['name' => '2. Endpoint', 'sequence_number' => 2, 'status' => TaskStatus::Open],
    ] as $index => $attributes) {
        $tasks[] = TaskModel::factory()->create([
            'project_id'      => $this->project->id,
            'task_list_id'    => $taskList->id,
            'key'             => 'MTM-'.($index + 1),
            'name'            => $attributes['name'],
            'sequence_number' => $attributes['sequence_number'],
            'status'          => $attributes['status']->value,
        ]);
    }

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$tasks[2]->key}");

    $response->assertOk()
        ->assertJsonPath('data.task_list_tasks.*.key', ['MTM-2', 'MTM-3', 'MTM-1'])
        ->assertJsonPath('data.task_list_tasks.1.name', '2. Endpoint')
        ->assertJsonPath('data.task_list_tasks.1.status', TaskStatus::Open->value);
});

it('shows an empty task list section for a task outside any list', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'task_list_id' => null]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}");

    $response->assertOk()->assertJsonPath('data.task_list_tasks', []);
});

it('breaks a name tie among the task list tasks by sequence number', function () {
    $taskList = TaskListModel::factory()->create(['project_id' => $this->project->id]);
    $later = TaskModel::factory()->create(['project_id' => $this->project->id, 'task_list_id' => $taskList->id, 'key' => 'MTM-9', 'name' => 'Same name', 'sequence_number' => 9]);
    TaskModel::factory()->create(['project_id' => $this->project->id, 'task_list_id' => $taskList->id, 'key' => 'MTM-4', 'name' => 'Same name', 'sequence_number' => 4]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$later->key}");

    $response->assertOk()->assertJsonPath('data.task_list_tasks.*.key', ['MTM-4', 'MTM-9']);
});
