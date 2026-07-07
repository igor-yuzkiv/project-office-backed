<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
    $this->task = TaskModel::factory()->create(['project_id' => $this->project->id]);
});

it('updates the comment content', function () {
    $comment = $this->task->comments()->create([
        'author_id' => $this->user->id,
        'content'   => 'Original content',
    ]);

    $response = $this->putJson(
        "/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$comment->id}",
        ['content' => 'Updated content']
    );

    $response->assertOk()
        ->assertJsonPath('data.content', 'Updated content');

    expect($comment->fresh()->content)->toBe('Updated content');
});

it('accepts content up to 10000 characters', function () {
    $comment = $this->task->comments()->create([
        'author_id' => $this->user->id,
        'content'   => 'Original content',
    ]);

    $content = str_repeat('a', 10000);

    $response = $this->putJson(
        "/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$comment->id}",
        ['content' => $content]
    );

    $response->assertOk()
        ->assertJsonPath('data.content', $content);
});

it('returns 404 when the comment does not belong to the task', function () {
    $otherTask = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $comment = $otherTask->comments()->create([
        'author_id' => $this->user->id,
        'content'   => 'Belongs to another task',
    ]);

    $response = $this->putJson(
        "/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$comment->id}",
        ['content' => 'Updated content']
    );

    $response->assertNotFound();
});

it('rejects updating a comment from a different author', function () {
    $otherUser = UserModel::factory()->create();
    $comment = $this->task->comments()->create([
        'author_id' => $otherUser->id,
        'content'   => 'Original content',
    ]);

    $response = $this->putJson(
        "/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$comment->id}",
        ['content' => 'Updated content']
    );

    $response->assertForbidden();
});
