<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

const PROJECT_ALL_ZERO_COUNTS = [
    'backlog'               => 0,
    'open'                  => 0,
    'ready_for_development' => 0,
    'in_progress'           => 0,
    'ready_to_test'         => 0,
    'completed'             => 0,
    'closed'                => 0,
    'declined'              => 0,
];

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
});

function projectWithTasks(array $statusCounts): ProjectModel
{
    $project = ProjectModel::factory()->create();
    foreach ($statusCounts as $status => $count) {
        TaskModel::factory()->count($count)->create([
            'project_id' => $project->id,
            'status'     => $status,
        ]);
    }

    return $project;
}

it('returns zeros for a project without tasks in index, search, show and pinned', function () {
    $project = ProjectModel::factory()->create();
    $this->user->pinnedProjects()->attach($project->id, ['created_at' => now()]);

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonPath('data.0.task_status_counts', PROJECT_ALL_ZERO_COUNTS);
    $this->postJson('/api/projects/search', ['query' => ''])
        ->assertOk()
        ->assertJsonPath('data.0.task_status_counts', PROJECT_ALL_ZERO_COUNTS);
    $this->getJson("/api/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.task_status_counts', PROJECT_ALL_ZERO_COUNTS);
    $this->getJson('/api/projects/pinned')
        ->assertOk()
        ->assertJsonPath('data.0.task_status_counts', PROJECT_ALL_ZERO_COUNTS);
});

it('shows counts for every status on a single project', function () {
    $project = projectWithTasks(['open' => 3, 'in_progress' => 1, 'completed' => 2, 'declined' => 1]);
    TaskModel::factory()->create(['project_id' => ProjectModel::factory()->create()->id, 'status' => TaskStatus::Open]);

    $this->getJson("/api/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.task_status_counts', [
            ...PROJECT_ALL_ZERO_COUNTS,
            'open'        => 3,
            'in_progress' => 1,
            'completed'   => 2,
            'declined'    => 1,
        ]);
});

it('carries counts in search results', function () {
    $project = projectWithTasks(['ready_to_test' => 2]);

    $this->postJson('/api/projects/search', ['query' => ''])
        ->assertOk()
        ->assertJsonPath('data.0.id', $project->id)
        ->assertJsonPath('data.0.task_status_counts.ready_to_test', 2);
});

it('counts each project in index separately with a single query', function () {
    $first = projectWithTasks(['backlog' => 2]);
    $second = projectWithTasks(['closed' => 1]);
    $third = projectWithTasks([]);

    DB::enableQueryLog();
    $response = $this->getJson('/api/projects?per_page=10')->assertOk()->assertJsonCount(3, 'data');
    $countQueries = array_filter(DB::getQueryLog(), fn (array $q) => str_contains($q['query'], 'group by "project_id", "status"'));
    DB::disableQueryLog();

    expect($countQueries)->toHaveCount(1);

    $byId = collect($response->json('data'))->keyBy('id');
    expect($byId[$first->id]['task_status_counts']['backlog'])->toBe(2)
        ->and($byId[$second->id]['task_status_counts']['closed'])->toBe(1)
        ->and($byId[$third->id]['task_status_counts'])->toBe(PROJECT_ALL_ZERO_COUNTS);
});

it('counts each project in search separately with a single query', function () {
    $first = projectWithTasks(['open' => 1]);
    $second = projectWithTasks(['completed' => 3]);
    $third = projectWithTasks([]);

    DB::enableQueryLog();
    $response = $this->postJson('/api/projects/search?per_page=10', ['query' => ''])->assertOk()->assertJsonCount(3, 'data');
    $countQueries = array_filter(DB::getQueryLog(), fn (array $q) => str_contains($q['query'], 'group by "project_id", "status"'));
    DB::disableQueryLog();

    expect($countQueries)->toHaveCount(1);

    $byId = collect($response->json('data'))->keyBy('id');
    expect($byId[$first->id]['task_status_counts']['open'])->toBe(1)
        ->and($byId[$second->id]['task_status_counts']['completed'])->toBe(3)
        ->and($byId[$third->id]['task_status_counts'])->toBe(PROJECT_ALL_ZERO_COUNTS);
});

it('keeps the counts out of the CLI project payloads', function () {
    $project = projectWithTasks(['open' => 1]);

    $this->getJson("/api/cli/projects/{$project->id}")
        ->assertOk()
        ->assertJsonMissingPath('data.task_status_counts');
});
