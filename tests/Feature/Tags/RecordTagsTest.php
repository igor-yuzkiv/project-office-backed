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

// --- Task tags ---

it('returns the tags attached to a task', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $tagA = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $tagB = TagModel::create(['name' => 'beta', 'color' => '#222222']);
    $task->tags()->attach($tagA->id);
    $task->tags()->attach($tagB->id);

    $response = $this->getJson("/api/tasks/{$task->id}");

    $response->assertOk();
    $names = collect($response->json('data.tags'))->pluck('name')->sort()->values()->all();
    expect($names)->toBe(['alpha', 'beta']);
    expect($response->json('data.tags'))->toHaveCount(2);
});

it('returns an empty tags list when a task has no tags', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->getJson("/api/tasks/{$task->id}");

    $response->assertOk();
    expect($response->json('data.tags'))->toHaveCount(0);
});

it('returns 404 for a non-existent task', function () {
    $this->getJson('/api/tasks/99999')->assertNotFound();
});

// --- Project tags ---

it('returns the tags attached to a project', function () {
    $tagA = TagModel::create(['name' => 'backend', 'color' => '#AAAAAA']);
    $tagB = TagModel::create(['name' => 'frontend', 'color' => '#BBBBBB']);
    $this->project->tags()->attach($tagA->id);
    $this->project->tags()->attach($tagB->id);

    $response = $this->getJson("/api/projects/{$this->project->id}");

    $response->assertOk();
    $names = collect($response->json('data.tags'))->pluck('name')->sort()->values()->all();
    expect($names)->toBe(['backend', 'frontend']);
    expect($response->json('data.tags'))->toHaveCount(2);
});

it('returns an empty tags list when a project has no tags', function () {
    $response = $this->getJson("/api/projects/{$this->project->id}");

    $response->assertOk();
    expect($response->json('data.tags'))->toHaveCount(0);
});

it('returns 404 for a non-existent project', function () {
    $this->getJson('/api/projects/99999')->assertNotFound();
});
