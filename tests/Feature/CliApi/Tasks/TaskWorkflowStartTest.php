<?php

use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
});

it('sets the task status to in_progress and returns the task with recent comments', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open->value]);
    $author = UserModel::factory()->create();
    CommentModel::factory()->count(2)->create(['commentable_id' => $task->id, 'commentable_type' => TaskModel::class, 'author_id' => $author->id]);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/start");

    $response->assertOk();
    expect($response->json('task.status'))->toBe(TaskStatus::InProgress->value);
    expect($response->json('comments'))->toHaveCount(2);
    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('is idempotent when the task is already in_progress', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::InProgress->value]);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/start");

    $response->assertOk();
    expect($response->json('task.status'))->toBe(TaskStatus::InProgress->value);
    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('creates a comment with the start marker when a comment is given', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open->value]);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/start", [
        'comment' => 'Picking this up now.',
    ]);

    $response->assertOk();
    expect($response->json('comments'))->toHaveCount(1);

    $comment = CommentModel::query()->where('commentable_id', $task->id)->sole();
    expect($comment->content)->toContain('# Start')->toContain('Picking this up now.');
});

it('does not create a comment when none is given', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open->value]);

    $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/start")->assertOk();

    expect(CommentModel::query()->where('commentable_id', $task->id)->count())->toBe(0);
});

it('records nothing on a repeated start of a task already in progress', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open->value]);
    $url = "/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/start";

    $this->postJson($url)->assertOk();
    $this->postJson($url)->assertOk();

    expect(AuditRecordModel::query()->count())->toBe(1);
});

it('records exactly one task.started event even when a comment is supplied', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open->value]);

    $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/start", [
        'comment' => 'Picking this up.',
    ])->assertOk();

    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('task.started')
        ->and($record->title)->toBe("{$this->user->name} started {$task->key}")
        ->and($record->description)->toBe($task->name)
        ->and($record->subject_type)->toBe(TaskModel::class)
        ->and($record->subject_id)->toBe($task->id);
});

it('returns the tasks of the task list in plan order, the started task among them', function () {
    $taskList = TaskListModel::factory()->create(['project_id' => $this->project->id]);
    $tasks = [];
    // Names run against sequence numbers and statuses, so the assertion fails if the order
    // ever falls back to either. "10." must land after "2." — the column's collation, not the code.
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

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$tasks[2]->id}/workflow/start");

    $response->assertOk()
        ->assertJsonPath('task.task_list_tasks.*.key', ['MTM-2', 'MTM-3', 'MTM-1'])
        ->assertJsonPath('task.task_list_tasks.*.name', ['1. Data model', '2. Endpoint', '10. Ship it'])
        ->assertJsonPath('task.task_list_tasks.*.status', [TaskStatus::Backlog->value, TaskStatus::InProgress->value, TaskStatus::Closed->value]);
});

it('returns an empty task list section for a task outside any list', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'task_list_id' => null]);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/start");

    $response->assertOk()->assertJsonPath('task.task_list_tasks', []);
});
