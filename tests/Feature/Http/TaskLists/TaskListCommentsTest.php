<?php

use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->taskList = TaskListModel::factory()->create([
        'project_id' => ProjectModel::factory()->create()->id,
    ]);
});

it('creates a comment through the task list route', function () {
    $response = $this->postJson("/api/task-lists/{$this->taskList->id}/comments", [
        'content' => 'First note',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.content', 'First note')
        ->assertJsonPath('data.author.id', $this->user->id);

    expect($this->taskList->comments()->count())->toBe(1);
});

it('reads comments through the task list route, newest first', function () {
    $older = CommentModel::factory()->create([
        'commentable_id'   => $this->taskList->id,
        'commentable_type' => TaskListModel::class,
        'author_id'        => $this->user->id,
        'created_at'       => '2026-01-01 10:00:00',
    ]);
    $newer = CommentModel::factory()->create([
        'commentable_id'   => $this->taskList->id,
        'commentable_type' => TaskListModel::class,
        'author_id'        => $this->user->id,
        'created_at'       => '2026-02-01 10:00:00',
    ]);

    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/comments");

    $response->assertOk();
    expect(collect($response->json('data'))->pluck('id')->all())
        ->toBe([(string) $newer->id, (string) $older->id]);
});

it('does not return comments of another task list', function () {
    $otherList = TaskListModel::factory()->create(['project_id' => $this->taskList->project_id]);
    CommentModel::factory()->create([
        'commentable_id'   => $otherList->id,
        'commentable_type' => TaskListModel::class,
        'author_id'        => $this->user->id,
    ]);

    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/comments");

    $response->assertOk();
    expect($response->json('data'))->toBeEmpty();
});

it('rejects an empty comment', function () {
    $this->postJson("/api/task-lists/{$this->taskList->id}/comments", ['content' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors('content');
});

it('requires authentication', function () {
    auth()->forgetGuards();

    $this->getJson("/api/task-lists/{$this->taskList->id}/comments")->assertUnauthorized();
});

// Editing and deleting stay on the universal comment routes — no consumer-scoped
// endpoints are added for them.
it('edits and deletes a task list comment through the universal routes', function () {
    $created = $this->postJson("/api/task-lists/{$this->taskList->id}/comments", ['content' => 'Draft'])
        ->assertCreated()
        ->json('data.id');

    $this->patchJson("/api/comments/{$created}", ['content' => 'Edited'])
        ->assertOk()
        ->assertJsonPath('data.content', 'Edited');

    $this->deleteJson("/api/comments/{$created}")->assertOk();

    expect($this->taskList->comments()->count())->toBe(0);
});
