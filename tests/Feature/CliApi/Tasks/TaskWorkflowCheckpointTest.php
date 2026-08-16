<?php

use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
});

it('creates a comment with the checkpoint marker and does not change the status', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::InProgress->value]);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/checkpoint", [
        'subject' => 'Investigated the bug',
        'comment' => 'Root cause found in the parser.',
    ]);

    $response->assertCreated();

    $comment = CommentModel::query()->where('commentable_id', $task->id)->sole();
    expect($comment->content)
        ->toContain('# Checkpoint')
        ->toContain('Investigated the bug')
        ->toContain('Root cause found in the parser.');

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('touches the task updated_at timestamp', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::InProgress->value]);
    $originalUpdatedAt = $task->updated_at;

    $this->travel(1)->minute();

    $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/checkpoint", [
        'subject' => 'Investigated the bug',
        'comment' => 'Root cause found in the parser.',
    ])->assertCreated();

    expect($task->fresh()->updated_at)->not->toEqual($originalUpdatedAt);
});

it('requires subject and comment', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/checkpoint", []);

    $response->assertUnprocessable()->assertJsonValidationErrors(['subject', 'comment']);
});

it('records exactly one task.checkpoint event built from the command fields', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::InProgress->value]);

    $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/checkpoint", [
        'subject' => 'Investigated the bug',
        'comment' => 'Root cause found in the parser.',
    ])->assertCreated();

    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('task.checkpoint')
        ->and($record->title)->toBe("{$this->user->name} recorded a checkpoint on {$task->key}")
        ->and($record->description)->toBe('Investigated the bug — Root cause found in the parser.');
});

it('cuts a long excerpt on a character boundary', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::InProgress->value]);
    $comment = str_repeat('я', 600);

    $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/checkpoint", [
        'subject' => 'Long one',
        'comment' => $comment,
    ])->assertCreated();

    $excerpt = Str::after(AuditRecordModel::query()->sole()->description, ' — ');

    expect(mb_check_encoding($excerpt, 'UTF-8'))->toBeTrue()
        ->and(mb_strlen($excerpt))->toBe(501)
        ->and($excerpt)->toEndWith('…');
});
