<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Actions\AddTasksToTaskList\AddTasksToTaskListCommand;
use App\Domains\TaskList\Actions\AddTasksToTaskList\AddTasksToTaskListHandler;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
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

it('rejects more than a hundred task ids', function () {
    $taskIds = TaskModel::factory()
        ->count(101)
        ->create(['project_id' => $this->project->id, 'task_list_id' => null])
        ->pluck('id')
        ->all();

    $this->postJson("/api/task-lists/{$this->taskList->id}/tasks", ['task_ids' => $taskIds])
        ->assertStatus(422)
        ->assertJsonValidationErrors('task_ids');
});

it('rejects a repeated task id', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'task_list_id' => null]);

    $this->postJson("/api/task-lists/{$this->taskList->id}/tasks", ['task_ids' => [$task->id, $task->id]])
        ->assertStatus(422)
        ->assertJsonValidationErrors('task_ids.0');
});

it('requires authentication', function () {
    auth()->forgetGuards();

    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'task_list_id' => null]);

    $this->postJson("/api/task-lists/{$this->taskList->id}/tasks", ['task_ids' => [$task->id]])
        ->assertUnauthorized();

    expect($task->fresh()->task_list_id)->toBeNull();
});

it('records one task_list.tasks_added event with the keys in a stable order', function () {
    $tasks = TaskModel::factory()->count(3)->create([
        'project_id'   => $this->project->id,
        'task_list_id' => null,
    ]);

    $this->postJson("/api/task-lists/{$this->taskList->id}/tasks", [
        'task_ids' => $tasks->pluck('id')->all(),
    ])->assertOk();

    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('task_list.tasks_added')
        ->and($record->title)->toBe("{$this->user->name} added 3 tasks to «{$this->taskList->name}»")
        ->and($record->description)->toBe($tasks->pluck('key')->sort()->implode(', '))
        ->and($record->subject_type)->toBe(TaskListModel::class)
        ->and($record->subject_id)->toBe($this->taskList->id);
});

it('records nothing when the handler claims no task at all', function () {
    // Straight to the handler: the request layer rejects a foreign task with 422, so an HTTP
    // call never reaches the empty-selection guard this test is about.
    $foreign = TaskModel::factory()->create([
        'project_id'   => ProjectModel::factory()->create()->id,
        'task_list_id' => null,
    ]);

    app(AddTasksToTaskListHandler::class)->handle(
        new AddTasksToTaskListCommand($this->taskList, [$foreign->id])
    );

    expect(AuditRecordModel::query()->count())->toBe(0)
        ->and($foreign->fresh()->task_list_id)->toBeNull();
});
