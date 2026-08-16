<?php

use App\Domains\Task\Services\TaskViewRegistry;
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
// The frontend replays this exact payload into POST /api/tasks/search, and the dashboard counts
// the same way, so a renamed key puts different numbers on Home and on the Tasks page.
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

it('gives an unfiltered view an empty filter list, not null', function () {
    $view = $this->getJson('/api/task-views')->assertOk()->json('data.0');

    // assertJsonPath(..., []) cannot tell an empty array from a missing key, and the frontend
    // spreads this value — null would throw there rather than fail here.
    expect($view['key'])->toBe('all')
        ->and($view['filters'])->toBeArray()->toBeEmpty();
});
