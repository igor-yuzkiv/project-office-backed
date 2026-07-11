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
    expect($comment->content)->toContain('[Start]')->toContain('Picking this up now.');
});

it('does not create a comment when none is given', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::Open->value]);

    $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/start")->assertOk();

    expect(CommentModel::query()->where('commentable_id', $task->id)->count())->toBe(0);
});
