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

it('attaches tags to a task on create when tag_ids is provided', function () {
    $tagA = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $tagB = TagModel::create(['name' => 'beta', 'color' => '#222222']);

    $response = $this->postJson('/api/tasks', [
        'project_id' => $this->project->id,
        'name'       => 'Tagged Task',
        'tag_ids'    => [$tagA->id, $tagB->id],
    ]);

    $response->assertCreated();

    $task = TaskModel::findOrFail($response->json('data.id'));
    expect($task->tags()->pluck('tags.id')->sort()->values()->all())
        ->toBe(collect([$tagA->id, $tagB->id])->sort()->values()->all());
});

it('syncs tags on update — adds new, removes absent', function () {
    $tagA = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $tagB = TagModel::create(['name' => 'beta', 'color' => '#222222']);
    $tagC = TagModel::create(['name' => 'gamma', 'color' => '#333333']);

    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $task->tags()->attach([$tagA->id, $tagB->id]);

    $response = $this->patchJson("/api/tasks/{$task->id}", [
        'name'    => $task->name,
        'status'  => TaskStatus::Open->value,
        'tag_ids' => [$tagB->id, $tagC->id],
    ]);

    $response->assertOk();

    $attachedIds = $task->fresh()->tags()->pluck('tags.id')->sort()->values()->all();
    expect($attachedIds)->toBe(collect([$tagB->id, $tagC->id])->sort()->values()->all());
});

it('removes all tags when tag_ids is an empty array', function () {
    $tagA = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $tagB = TagModel::create(['name' => 'beta', 'color' => '#222222']);

    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $task->tags()->attach([$tagA->id, $tagB->id]);

    $response = $this->patchJson("/api/tasks/{$task->id}", [
        'name'    => $task->name,
        'status'  => TaskStatus::Open->value,
        'tag_ids' => [],
    ]);

    $response->assertOk();

    expect($task->fresh()->tags()->count())->toBe(0);
});

it('does not change tags when tag_ids is absent from the payload', function () {
    $tagA = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $tagB = TagModel::create(['name' => 'beta', 'color' => '#222222']);

    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $task->tags()->attach([$tagA->id, $tagB->id]);

    $response = $this->patchJson("/api/tasks/{$task->id}", [
        'name'   => $task->name,
        'status' => TaskStatus::Open->value,
    ]);

    $response->assertOk();

    $attachedIds = $task->fresh()->tags()->pluck('tags.id')->sort()->values()->all();
    expect($attachedIds)->toBe(collect([$tagA->id, $tagB->id])->sort()->values()->all());
});
