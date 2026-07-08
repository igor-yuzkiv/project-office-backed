<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

it('updates the status and description', function () {
    $task = TaskModel::factory()->create([
        'project_id'  => $this->project->id,
        'status'      => TaskStatus::Open->value,
        'description' => 'Original description',
    ]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'status'      => TaskStatus::Closed->value,
        'description' => 'Updated description',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', TaskStatus::Closed->value)
        ->assertJsonPath('data.description', 'Updated description');
});

it('updates the name', function () {
    $task = TaskModel::factory()->create([
        'project_id' => $this->project->id,
        'name'       => 'Original name',
    ]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'name' => 'Renamed task',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Renamed task');

    expect($task->fresh()->name)->toBe('Renamed task');
});

it('ignores task_list_id, priority, and dates', function () {
    $task = TaskModel::factory()->create([
        'project_id' => $this->project->id,
        'name'       => 'Original name',
    ]);
    $original = $task->fresh();

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'task_list_id' => '01k00000000000000000000000',
        'priority'     => 100,
        'start_date'   => '2026-01-01',
        'due_date'     => '2026-01-02',
        'status'       => TaskStatus::Closed->value,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', TaskStatus::Closed->value);

    $fresh = $task->fresh();
    expect($fresh->task_list_id)->toBe($original->task_list_id);
    expect($fresh->priority)->toBe($original->priority);
    expect($fresh->start_date?->toIso8601String())->toBe($original->start_date?->toIso8601String());
    expect($fresh->due_date?->toIso8601String())->toBe($original->due_date?->toIso8601String());
});

it('replaces tags from a comma-separated tags string', function () {
    $existing = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $task->tags()->attach([$existing->id]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'tags' => 'alpha,beta',
    ]);

    $response->assertOk();

    $names = $task->fresh()->tags()->pluck('name')->sort()->values()->all();
    expect($names)->toBe(['alpha', 'beta']);
});

it('reuses an existing tag matched by normalized name and does not duplicate it', function () {
    $existing = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'tags' => 'Alpha,beta',
    ]);

    $response->assertOk();

    $tagIds = $task->fresh()->tags()->pluck('tags.id')->sort()->values()->all();
    expect($tagIds)->toContain($existing->id);
    expect(TagModel::where('name', 'alpha')->count())->toBe(1);
});

it('clears tags when tags is an empty string', function () {
    $existing = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $task->tags()->attach([$existing->id]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'tags' => '',
    ]);

    $response->assertOk();

    expect($task->fresh()->tags()->count())->toBe(0);
});

it('does not change tags when tags is absent from the payload', function () {
    $existing = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $task->tags()->attach([$existing->id]);

    $response = $this->putJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}", [
        'status' => TaskStatus::Closed->value,
    ]);

    $response->assertOk();

    expect($task->fresh()->tags()->pluck('id')->all())->toBe([$existing->id]);
});
