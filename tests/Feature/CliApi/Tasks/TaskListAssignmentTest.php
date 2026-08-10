<?php

use App\Domains\Project\Models\ProjectModel;
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

it('creates a task in the list addressed by key', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name'         => 'Write the migration',
        'task_list_id' => 'MTM-TL-7',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.task_list.key', 'MTM-TL-7');

    expect(TaskModel::firstOrFail()->task_list_id)->toBe($this->taskList->id);
});

it('creates a task in the list addressed by ulid', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name'         => 'Write the migration',
        'task_list_id' => $this->taskList->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.task_list.key', 'MTM-TL-7');
});

it('creates a task without a list when the field is absent', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name' => 'Loose task',
    ]);

    $response->assertCreated();

    expect(TaskModel::firstOrFail()->task_list_id)->toBeNull();
});

it('moves a task into a list on update', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'task_list_id' => 'MTM-TL-7',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.task_list.key', 'MTM-TL-7');

    expect($task->fresh()->task_list_id)->toBe($this->taskList->id);
});

it('rejects a task list from another project', function () {
    $otherProject = ProjectModel::factory()->create(['prefix' => 'OTH']);
    $foreign = TaskListModel::factory()->create([
        'project_id'      => $otherProject->id,
        'key'             => 'OTH-TL-1',
        'sequence_number' => 1,
    ]);

    $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name'         => 'Sneaky task',
        'task_list_id' => 'OTH-TL-1',
    ])->assertUnprocessable()->assertJsonValidationErrors(['task_list_id']);

    $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name'         => 'Sneaky task',
        'task_list_id' => $foreign->id,
    ])->assertUnprocessable()->assertJsonValidationErrors(['task_list_id']);
});

it('rejects a task list from another project on update', function () {
    $otherProject = ProjectModel::factory()->create(['prefix' => 'OTH']);
    TaskListModel::factory()->create([
        'project_id'      => $otherProject->id,
        'key'             => 'OTH-TL-1',
        'sequence_number' => 1,
    ]);
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'task_list_id' => 'OTH-TL-1',
    ])->assertUnprocessable()->assertJsonValidationErrors(['task_list_id']);
});

it('keeps the current list when the field is absent from an update', function () {
    $task = TaskModel::factory()->create([
        'project_id'   => $this->project->id,
        'task_list_id' => $this->taskList->id,
    ]);

    $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'name' => 'Renamed task',
    ])->assertOk()->assertJsonPath('data.task_list.key', 'MTM-TL-7');

    expect($task->fresh()->task_list_id)->toBe($this->taskList->id);
});

it('rejects an unknown task list key', function () {
    $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name'         => 'Sneaky task',
        'task_list_id' => 'MTM-TL-999',
    ])->assertUnprocessable()->assertJsonValidationErrors(['task_list_id']);
});
