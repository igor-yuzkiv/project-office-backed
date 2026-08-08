<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
});

function searchTaskLists(array $filters): TestResponse
{
    return test()->postJson('/api/task-lists/search', ['query' => '', 'filters' => $filters]);
}

it('filters the list by status', function () {
    TaskListModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskListStatus::Open->value]);
    TaskListModel::factory()->count(2)->create(['project_id' => $this->project->id, 'status' => TaskListStatus::Completed->value]);

    $response = searchTaskLists([[
        'filter_key' => 'text',
        'field_name' => 'status',
        'value'      => 'completed',
        'matchMode'  => 'equals',
        'params'     => [],
    ]]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

it('filters the list by tags', function () {
    $tag = TagModel::factory()->create();
    $tagged = TaskListModel::factory()->create(['project_id' => $this->project->id]);
    $tagged->tags()->sync([$tag->id]);
    TaskListModel::factory()->create(['project_id' => $this->project->id]);

    $response = searchTaskLists([[
        'filter_key' => 'tags',
        'field_name' => null,
        'value'      => [$tag->id],
        'matchMode'  => null,
        'params'     => [],
    ]]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(1)
        ->and($response->json('data.0.id'))->toBe($tagged->id);
});

it('filters the list by project', function () {
    $otherProject = ProjectModel::factory()->create();
    TaskListModel::factory()->count(2)->create(['project_id' => $this->project->id]);
    TaskListModel::factory()->create(['project_id' => $otherProject->id]);

    $response = searchTaskLists([[
        'filter_key' => 'text',
        'field_name' => 'project_id',
        'value'      => $this->project->id,
        'matchMode'  => 'equals',
        'params'     => [],
    ]]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});
