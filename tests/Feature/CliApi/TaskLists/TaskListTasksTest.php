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
    $this->project = ProjectModel::factory()->create(['prefix' => 'MTM']);
    $this->taskList = TaskListModel::factory()->create([
        'project_id'      => $this->project->id,
        'key'             => 'MTM-TL-7',
        'sequence_number' => 7,
    ]);
});

it('returns the tasks of the list in sequence order, every status included', function () {
    foreach ([
        ['sequence_number' => 3, 'status' => TaskStatus::Closed, 'key' => 'MTM-3'],
        ['sequence_number' => 1, 'status' => TaskStatus::Backlog, 'key' => 'MTM-1'],
        ['sequence_number' => 2, 'status' => TaskStatus::Open, 'key' => 'MTM-2'],
    ] as $task) {
        TaskModel::factory()->create([
            'project_id'      => $this->project->id,
            'task_list_id'    => $this->taskList->id,
            'sequence_number' => $task['sequence_number'],
            'key'             => $task['key'],
            'status'          => $task['status']->value,
        ]);
    }

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/tasks");

    $response->assertOk()
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('data.0.key', 'MTM-1')
        ->assertJsonPath('data.1.key', 'MTM-2')
        ->assertJsonPath('data.2.key', 'MTM-3');
});

it('reaches the same list by key and by ulid', function () {
    TaskModel::factory()->create([
        'project_id'   => $this->project->id,
        'task_list_id' => $this->taskList->id,
        'key'          => 'MTM-1',
    ]);

    $byKey = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/tasks");
    $byUlid = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/{$this->taskList->id}/tasks");

    $byKey->assertOk();
    $byUlid->assertOk();

    expect($byKey->json('data'))->toBe($byUlid->json('data'));
});

it('paginates the tasks', function () {
    foreach (range(1, 3) as $i) {
        TaskModel::factory()->create([
            'project_id'      => $this->project->id,
            'task_list_id'    => $this->taskList->id,
            'sequence_number' => $i,
            'key'             => "MTM-{$i}",
        ]);
    }

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/tasks?per_page=2&page=2");

    $response->assertOk()
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('data.0.key', 'MTM-3');

    expect($response->json('data'))->toHaveCount(1);
});

it('does not return tasks outside the list', function () {
    TaskModel::factory()->create([
        'project_id' => $this->project->id,
        'key'        => 'MTM-1',
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/tasks");

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(0);
});

it('does not reach a task list of another project', function () {
    $otherProject = ProjectModel::factory()->create(['prefix' => 'OTH']);
    TaskListModel::factory()->create([
        'project_id'      => $otherProject->id,
        'key'             => 'OTH-TL-1',
        'sequence_number' => 1,
    ]);

    $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/OTH-TL-1/tasks")
        ->assertNotFound();
});

it('rejects an unauthenticated request', function () {
    auth()->forgetGuards();

    $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/tasks")
        ->assertUnauthorized();
});
