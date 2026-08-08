<?php

use App\Domains\Project\Models\ProjectModel;
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

it('adds unassigned tasks of the same project', function () {
    $tasks = TaskModel::factory()->count(2)->create([
        'project_id'   => $this->project->id,
        'task_list_id' => null,
    ]);

    $response = $this->postJson("/api/task-lists/{$this->taskList->id}/tasks", [
        'task_ids' => $tasks->pluck('id')->all(),
    ]);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2)
        ->and(TaskModel::whereIn('id', $tasks->pluck('id'))->pluck('task_list_id')->unique()->all())
        ->toBe([$this->taskList->id]);
});

it('records who filed the task into the list', function () {
    $task = TaskModel::factory()->create([
        'project_id'   => $this->project->id,
        'task_list_id' => null,
        'updated_by'   => UserModel::factory()->create()->id,
    ]);

    $this->postJson("/api/task-lists/{$this->taskList->id}/tasks", ['task_ids' => [$task->id]])
        ->assertOk();

    expect($task->fresh()->updated_by)->toBe(auth()->id());
});

it('rejects a task from another project', function () {
    $foreign = TaskModel::factory()->create([
        'project_id'   => ProjectModel::factory()->create()->id,
        'task_list_id' => null,
    ]);

    $this->postJson("/api/task-lists/{$this->taskList->id}/tasks", ['task_ids' => [$foreign->id]])
        ->assertStatus(422)
        ->assertJsonValidationErrors('task_ids.0');

    expect($foreign->fresh()->task_list_id)->toBeNull();
});

it('rejects a task that already belongs to a list', function () {
    $otherList = TaskListModel::factory()->create(['project_id' => $this->project->id]);
    $taken = TaskModel::factory()->create([
        'project_id'   => $this->project->id,
        'task_list_id' => $otherList->id,
    ]);

    $this->postJson("/api/task-lists/{$this->taskList->id}/tasks", ['task_ids' => [$taken->id]])
        ->assertStatus(422)
        ->assertJsonValidationErrors('task_ids.0');

    expect($taken->fresh()->task_list_id)->toBe($otherList->id);
});

it('does not remove tasks already associated with the list', function () {
    $existing = TaskModel::factory()->create([
        'project_id'   => $this->project->id,
        'task_list_id' => $this->taskList->id,
    ]);
    $added = TaskModel::factory()->create([
        'project_id'   => $this->project->id,
        'task_list_id' => null,
    ]);

    $this->postJson("/api/task-lists/{$this->taskList->id}/tasks", ['task_ids' => [$added->id]])
        ->assertOk();

    expect($existing->fresh()->task_list_id)->toBe($this->taskList->id)
        ->and($added->fresh()->task_list_id)->toBe($this->taskList->id);
});

it('requires at least one task id', function () {
    $this->postJson("/api/task-lists/{$this->taskList->id}/tasks", ['task_ids' => []])
        ->assertStatus(422)
        ->assertJsonValidationErrors('task_ids');
});
