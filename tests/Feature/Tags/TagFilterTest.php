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

it('returns tasks that have any of the specified tags (OR semantics)', function () {
    $tagA = TagModel::create(['name' => 'Tag A', 'color' => '#ff0000']);
    $tagB = TagModel::create(['name' => 'Tag B', 'color' => '#00ff00']);

    $taskWithTagA = TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Task A']);
    $taskWithTagB = TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Task B']);
    $taskWithNoTags = TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Task C']);

    $taskWithTagA->tags()->attach($tagA->id);
    $taskWithTagB->tags()->attach($tagB->id);

    $response = $this->postJson('/api/tasks/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'tags',
            'field_name' => null,
            'value'      => [$tagA->id, $tagB->id],
            'matchMode'  => null,
            'params'     => [],
        ]],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);

    $returnedNames = collect($response->json('data'))->pluck('name')->sort()->values()->all();
    expect($returnedNames)->toBe(['Task A', 'Task B']);
});

it('does not return tasks that have none of the specified tags', function () {
    $tagA = TagModel::create(['name' => 'Tag A', 'color' => '#ff0000']);
    $tagB = TagModel::create(['name' => 'Tag B', 'color' => '#00ff00']);

    TaskModel::factory()->create(['project_id' => $this->project->id, 'name' => 'Task without tags']);

    $response = $this->postJson('/api/tasks/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'tags',
            'field_name' => null,
            'value'      => [$tagA->id, $tagB->id],
            'matchMode'  => null,
            'params'     => [],
        ]],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(0);
});

it('does not filter when value is an empty array', function () {
    TaskModel::factory()->count(3)->create(['project_id' => $this->project->id]);

    $response = $this->postJson('/api/tasks/search', [
        'query'   => '',
        'filters' => [[
            'filter_key' => 'tags',
            'field_name' => null,
            'value'      => [],
            'matchMode'  => null,
            'params'     => [],
        ]],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(3);
});
