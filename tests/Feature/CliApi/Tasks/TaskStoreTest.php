<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('creates a task using the project id from the route', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name' => 'New Task',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Task')
        ->assertJsonPath('data.project_id', $this->project->id);
});

it('ignores a project_id provided in the request body', function () {
    $otherProject = ProjectModel::factory()->create();

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name'       => 'New Task',
        'project_id' => $otherProject->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.project_id', $this->project->id);
});

it('creates new tags from a comma-separated tags string', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name' => 'New Task',
        'tags' => 'bug, Backend , urgent',
    ]);

    $response->assertCreated();

    $task = TaskModel::findOrFail($response->json('data.id'));
    expect($task->tags()->pluck('name')->sort()->values()->all())
        ->toBe(['backend', 'bug', 'urgent']);
});

it('reuses an existing tag matched by normalized name', function () {
    $existing = TagModel::create(['name' => 'backend', 'color' => '#111111']);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name' => 'New Task',
        'tags' => 'Backend,frontend',
    ]);

    $response->assertCreated();

    $task = TaskModel::findOrFail($response->json('data.id'));
    expect($task->tags()->pluck('id')->contains($existing->id))->toBeTrue();
    expect(TagModel::where('name', 'backend')->count())->toBe(1);
});

it('ignores an empty tags string', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name' => 'New Task',
        'tags' => '',
    ]);

    $response->assertCreated();

    $task = TaskModel::findOrFail($response->json('data.id'));
    expect($task->tags()->count())->toBe(0);
});

it('rejects a tag name longer than 64 characters', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks", [
        'name' => 'New Task',
        'tags' => str_repeat('a', 65),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['tags']);
});
