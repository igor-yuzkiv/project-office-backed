<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
});

function bulkTask(array $attributes = []): TaskModel
{
    return TaskModel::factory()->create($attributes + [
        'project_id' => test()->project->id,
        'status'     => TaskStatus::Open->value,
    ]);
}

it('moves every listed task to the given status', function () {
    $tasks = collect(range(1, 3))->map(fn () => bulkTask());

    $response = $this->patchJson('/api/tasks/bulk-status', [
        'task_ids' => $tasks->pluck('id')->all(),
        'status'   => TaskStatus::InProgress->value,
    ]);

    $response->assertOk()->assertJsonPath('data.updated_count', 3);

    foreach ($tasks as $task) {
        expect($task->refresh()->status)->toBe(TaskStatus::InProgress);
    }
});

it('records the acting user as the updater', function () {
    $task = bulkTask();

    $this->patchJson('/api/tasks/bulk-status', [
        'task_ids' => [$task->id],
        'status'   => TaskStatus::Completed->value,
    ])->assertOk();

    expect($task->refresh()->updated_by)->toBe($this->user->id);
});

it('changes nothing when any task id is unknown', function () {
    $task = bulkTask();

    $response = $this->patchJson('/api/tasks/bulk-status', [
        'task_ids' => [$task->id, (string) Str::ulid()],
        'status'   => TaskStatus::Closed->value,
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors(['task_ids.1']);
    expect($task->refresh()->status)->toBe(TaskStatus::Open);
});

it('rejects an invalid payload', function (array $payload, string $invalidField) {
    $this->patchJson('/api/tasks/bulk-status', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$invalidField]);
})->with([
    'unknown status'  => [fn () => ['task_ids' => [(string) Str::ulid()], 'status' => 'nonsense'], 'status'],
    'missing status'  => [fn () => ['task_ids' => [(string) Str::ulid()]], 'status'],
    'empty task_ids'  => [fn () => ['task_ids' => [], 'status' => TaskStatus::Open->value], 'task_ids'],
    'missing task_ids' => [fn () => ['status' => TaskStatus::Open->value], 'task_ids'],
    'over one hundred task_ids' => [
        fn () => [
            'task_ids' => array_map(fn () => (string) Str::ulid(), range(1, 101)),
            'status'   => TaskStatus::Open->value,
        ],
        'task_ids',
    ],
]);

it('requires authentication', function () {
    $task = bulkTask();

    auth()->forgetGuards();

    $this->patchJson('/api/tasks/bulk-status', [
        'task_ids' => [$task->id],
        'status'   => TaskStatus::Closed->value,
    ])->assertUnauthorized();

    expect($task->refresh()->status)->toBe(TaskStatus::Open);
});
