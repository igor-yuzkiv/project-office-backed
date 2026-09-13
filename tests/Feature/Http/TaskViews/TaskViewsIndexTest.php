<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\Task\Queries\CountTasksPerTaskViewQuery;
use App\Domains\Task\Services\TaskViewRegistry;
use App\Domains\Task\ValueObjects\TaskViewCount;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
});

it('rejects an unauthenticated request', function () {
    auth()->forgetGuards();

    $this->getJson('/api/task-views')->assertUnauthorized();
});

it('returns every registry view, in registry order', function () {
    $views = TaskViewRegistry::all();

    $response = $this->getJson('/api/task-views')->assertOk();

    expect($response->json('data'))->toHaveCount(count($views));

    foreach ($views as $index => $view) {
        $response->assertJsonPath("data.{$index}.key", $view->key)
            ->assertJsonPath("data.{$index}.label", $view->label);
    }
});

// The keys are spelled out rather than derived from FilterPayload: deriving them would make the
// test agree with whatever the mapping currently emits, which is the one thing it must not do.
// The frontend replays this exact payload into POST /api/tasks/search, so a renamed key would put
// a count on a view that its own list does not match.
it('describes every filter with the five keys the search endpoint reads', function () {
    $data = $this->getJson('/api/task-views')->assertOk()->json('data');

    $filters = collect($data)->flatMap(fn (array $view): array => $view['filters']);

    expect($filters)->not->toBeEmpty();

    foreach ($filters as $filter) {
        expect(array_keys($filter))->toBe(['filter_key', 'field_name', 'value', 'matchMode', 'params']);
    }
});

it('keeps a status filter value as a list rather than flattening it', function () {
    $this->getJson('/api/task-views')
        ->assertOk()
        ->assertJsonPath('data.2.key', 'all_in_progress')
        ->assertJsonPath('data.2.filters.0.filter_key', 'text')
        ->assertJsonPath('data.2.filters.0.field_name', 'status')
        ->assertJsonPath('data.2.filters.0.matchMode', 'in')
        ->assertJsonPath('data.2.filters.0.value', [
            'ready_for_development',
            'in_progress',
            'ready_to_test',
            'completed',
        ]);
});

it('counts declined among the closed', function () {
    $this->getJson('/api/task-views')
        ->assertOk()
        ->assertJsonPath('data.3.key', 'all_closed')
        ->assertJsonPath('data.3.filters.0.value', ['closed', 'declined']);
});

it('gives an unfiltered view an empty filter list, not null', function () {
    $view = $this->getJson('/api/task-views')->assertOk()->json('data.0');

    // assertJsonPath(..., []) cannot tell an empty array from a missing key, and the frontend
    // spreads this value — null would throw there rather than fail here.
    expect($view['key'])->toBe('all')
        ->and($view['filters'])->toBeArray()->toBeEmpty();
});

it('counts the tasks of every view, independent of any search', function () {
    $project = ProjectModel::factory()->create();
    TaskModel::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::Open->value]);
    TaskModel::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::Closed->value]);
    TaskModel::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::Backlog->value]);

    $counts = collect($this->getJson('/api/task-views')->assertOk()->json('data'))->pluck('count', 'key');

    expect($counts['all'])->toBe(3)
        ->and($counts['all_open'])->toBe(1)
        ->and($counts['all_in_progress'])->toBe(0)
        ->and($counts['all_closed'])->toBe(1)
        ->and($counts['all_backlogged'])->toBe(1);
});

it('counts filtered tasks without paginating them', function () {
    $project = ProjectModel::factory()->create();
    TaskModel::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::Closed->value]);
    TaskModel::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::Backlog->value]);

    $counts = collect(app(CountTasksPerTaskViewQuery::class)->handle())
        ->mapWithKeys(fn (TaskViewCount $counted): array => [$counted->view->key => $counted->count]);

    expect($counts['all'])->toBe(2)
        ->and($counts['all_closed'])->toBe(1)
        ->and($counts['all_backlogged'])->toBe(1)
        ->and($counts['all_in_progress'])->toBe(0);
});
