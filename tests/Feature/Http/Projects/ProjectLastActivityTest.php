<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
});

function projectEvent(ProjectModel $project, string $title, int $minutesAgo, ?UserModel $actor = null): AuditRecordModel
{
    return AuditRecordModel::factory()->forSubject($project)->create([
        'title'      => $title,
        'created_by' => $actor ?? UserModel::factory(),
        'created_at' => now()->subMinutes($minutesAgo),
    ]);
}

it('is null for a project without events in index, search, show and pinned', function () {
    $project = ProjectModel::factory()->create();
    $this->user->pinnedProjects()->attach($project->id, ['created_at' => now()]);

    $this->getJson('/api/projects')->assertOk()->assertJsonPath('data.0.last_activity', null);
    $this->postJson('/api/projects/search', ['query' => ''])->assertOk()->assertJsonPath('data.0.last_activity', null);
    $this->getJson("/api/projects/{$project->id}")->assertOk()->assertJsonPath('data.last_activity', null);
    $this->getJson('/api/projects/pinned')->assertOk()->assertJsonPath('data.0.last_activity', null);
});

it('carries the newest event with its actor in index, search, show and pinned', function () {
    $actor = UserModel::factory()->create();
    $project = ProjectModel::factory()->create();
    $this->user->pinnedProjects()->attach($project->id, ['created_at' => now()]);
    projectEvent($project, 'Older event', 10);
    $latest = projectEvent($project, 'Latest event', 1, $actor);
    projectEvent(ProjectModel::factory()->create(), 'Someone else', 0);

    $expected = [
        'title'      => 'Latest event',
        'actor'      => ['id' => $actor->id, 'name' => $actor->name, 'initials' => $actor->initials(), 'avatar_url' => null],
        'created_at' => $latest->created_at->toJSON(),
    ];

    $lastActivityOf = fn (array $rows): ?array => collect($rows)->firstWhere('id', $project->id)['last_activity'];

    expect($lastActivityOf($this->getJson('/api/projects')->assertOk()->json('data')))->toBe($expected)
        ->and($lastActivityOf($this->postJson('/api/projects/search', ['query' => ''])->assertOk()->json('data')))->toBe($expected)
        ->and($this->getJson("/api/projects/{$project->id}")->assertOk()->json('data.last_activity'))->toBe($expected)
        ->and($this->getJson('/api/projects/pinned')->assertOk()->json('data.0.last_activity'))->toBe($expected);
});

it('keeps the actor null when the author is gone', function () {
    $project = ProjectModel::factory()->create();
    AuditRecordModel::factory()->forSubject($project)->create(['created_by' => null]);

    $this->getJson("/api/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.last_activity.actor', null);
});

it('resolves the newest event of every project on a page with a single query', function () {
    $first = ProjectModel::factory()->create();
    $second = ProjectModel::factory()->create();
    $third = ProjectModel::factory()->create();
    projectEvent($first, 'First older', 5);
    projectEvent($first, 'First latest', 2);
    projectEvent($second, 'Second latest', 3);

    DB::enableQueryLog();
    $response = $this->getJson('/api/projects?per_page=10')->assertOk()->assertJsonCount(3, 'data');
    $activityQueries = array_filter(DB::getQueryLog(), fn (array $q) => str_contains($q['query'], 'distinct on (project_id)'));
    DB::disableQueryLog();

    expect($activityQueries)->toHaveCount(1);

    $byId = collect($response->json('data'))->keyBy('id');
    expect($byId[$first->id]['last_activity']['title'])->toBe('First latest')
        ->and($byId[$second->id]['last_activity']['title'])->toBe('Second latest')
        ->and($byId[$third->id]['last_activity'])->toBeNull();
});

it('keeps last_activity out of the CLI project payload', function () {
    $project = ProjectModel::factory()->create();
    projectEvent($project, 'Event', 1);

    $this->getJson("/api/cli/projects/{$project->id}")
        ->assertOk()
        ->assertJsonMissingPath('data.last_activity');
});
