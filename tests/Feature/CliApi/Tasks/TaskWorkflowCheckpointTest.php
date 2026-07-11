<?php

use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
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
        ->toContain('[Checkpoint]')
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
