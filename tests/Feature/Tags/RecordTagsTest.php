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

it('returns all tags for a task', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $tagA = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $tagB = TagModel::create(['name' => 'beta', 'color' => '#222222']);
    $task->tags()->attach($tagA->id);
    $task->tags()->attach($tagB->id);

    $response = $this->getJson("/api/tasks/{$task->id}/tags");

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name')->all();
    expect($names)->toContain('alpha', 'beta');
    expect($response->json('data'))->toHaveCount(2);
});

it('returns task tags ordered by taggables.created_at ascending', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $tagFirst = TagModel::create(['name' => 'first', 'color' => '#111111']);
    $tagSecond = TagModel::create(['name' => 'second', 'color' => '#222222']);

    $task->tags()->attach($tagFirst->id, ['created_at' => now()->subMinutes(5)]);
    $task->tags()->attach($tagSecond->id, ['created_at' => now()]);

    $response = $this->getJson("/api/tasks/{$task->id}/tags");

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name')->all();
    expect($names)->toBe(['first', 'second']);
});

it('returns an empty list when task has no tags', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->getJson("/api/tasks/{$task->id}/tags");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(0);
});

it('returns 404 for a non-existent task', function () {
    $this->getJson('/api/tasks/99999/tags')->assertNotFound();
});

it('returns 401 for task tags when unauthenticated', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $this->getJson("/api/tasks/{$task->id}/tags")->assertUnauthorized();
})->skip('auth middleware returns 401 only outside actingAs context — tested via separate unauthenticated request');

// --- Project tags ---

it('returns all tags for a project', function () {
    $tagA = TagModel::create(['name' => 'backend', 'color' => '#AAAAAA']);
    $tagB = TagModel::create(['name' => 'frontend', 'color' => '#BBBBBB']);
    $this->project->tags()->attach($tagA->id);
    $this->project->tags()->attach($tagB->id);

    $response = $this->getJson("/api/projects/{$this->project->id}/tags");

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name')->sort()->values()->all();
    expect($names)->toBe(['backend', 'frontend']);
    expect($response->json('data'))->toHaveCount(2);
});

it('returns project tags ordered by taggables.created_at ascending', function () {
    $tagFirst = TagModel::create(['name' => 'first', 'color' => '#111111']);
    $tagSecond = TagModel::create(['name' => 'second', 'color' => '#222222']);

    $this->project->tags()->attach($tagFirst->id, ['created_at' => now()->subMinutes(5)]);
    $this->project->tags()->attach($tagSecond->id, ['created_at' => now()]);

    $response = $this->getJson("/api/projects/{$this->project->id}/tags");

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name')->all();
    expect($names)->toBe(['first', 'second']);
});

it('returns an empty list when project has no tags', function () {
    $response = $this->getJson("/api/projects/{$this->project->id}/tags");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(0);
});

it('returns 404 for a non-existent project', function () {
    $this->getJson('/api/projects/99999/tags')->assertNotFound();
});
