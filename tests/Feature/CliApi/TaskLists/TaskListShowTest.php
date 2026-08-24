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
        'name'            => 'Release plan',
        'description'     => 'What ships this week',
    ]);
});

it('shows the task list by key and by ulid alike', function () {
    $byKey = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7");
    $byUlid = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/{$this->taskList->id}");

    $byKey->assertOk()->assertJsonPath('data.key', 'MTM-TL-7');
    $byUlid->assertOk();

    expect($byKey->json('data'))->toBe($byUlid->json('data'));
});

it('includes tasks of every status, in name order', function () {
    foreach ([
        ['name' => 'Ship the release notes', 'status' => TaskStatus::Closed, 'key' => 'MTM-3'],
        ['name' => 'Audit the changelog', 'status' => TaskStatus::Backlog, 'key' => 'MTM-1'],
        ['name' => 'Freeze the branch', 'status' => TaskStatus::Open, 'key' => 'MTM-2'],
    ] as $index => $task) {
        TaskModel::factory()->create([
            'project_id'      => $this->project->id,
            'task_list_id'    => $this->taskList->id,
            'sequence_number' => 3 - $index,
            'key'             => $task['key'],
            'name'            => $task['name'],
            'status'          => $task['status']->value,
        ]);
    }

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7");

    // Alphabetical, not the order they were written and not their sequence numbers: the reader
    // of a list looks things up by name.
    $response->assertOk()
        ->assertJsonPath('data.tasks.0.name', 'Audit the changelog')
        ->assertJsonPath('data.tasks.1.name', 'Freeze the branch')
        ->assertJsonPath('data.tasks.2.name', 'Ship the release notes')
        ->assertJsonPath('data.tasks.0.status', TaskStatus::Backlog->value)
        ->assertJsonPath('data.tasks.1.status', TaskStatus::Open->value)
        ->assertJsonPath('data.tasks.2.status', TaskStatus::Closed->value);
});

it('does not resolve a task list key belonging to another project', function () {
    $otherProject = ProjectModel::factory()->create(['prefix' => 'OTH']);
    $foreign = TaskListModel::factory()->create([
        'project_id'      => $otherProject->id,
        'key'             => 'OTH-TL-1',
        'sequence_number' => 1,
    ]);

    $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/OTH-TL-1")->assertNotFound();
    $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/{$foreign->id}")->assertNotFound();
});
