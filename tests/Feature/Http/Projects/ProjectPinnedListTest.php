<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
});

it('returns an empty list when nothing is pinned', function () {
    $this->actingAs($this->user)
        ->getJson('/api/projects/pinned')
        ->assertOk()
        ->assertExactJson(['data' => []]);
});

it('lists pinned projects in the order they were pinned', function () {
    [$first, $second, $third] = ProjectModel::factory()->count(3)->create();

    $this->user->pinnedProjects()->attach($second->id, ['created_at' => now()->subMinutes(2)]);
    $this->user->pinnedProjects()->attach($third->id, ['created_at' => now()->subMinute()]);
    $this->user->pinnedProjects()->attach($first->id, ['created_at' => now()]);

    $this->actingAs($this->user)
        ->getJson('/api/projects/pinned')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.id', $second->id)
        ->assertJsonPath('data.1.id', $third->id)
        ->assertJsonPath('data.2.id', $first->id)
        ->assertJsonPath('data.0.is_pinned', true);
});

it('counts tasks per status with every status present', function () {
    $project = ProjectModel::factory()->create();
    $this->user->pinnedProjects()->attach($project->id, ['created_at' => now()]);

    TaskModel::factory()->count(3)->create(['project_id' => $project->id, 'status' => TaskStatus::Open]);
    TaskModel::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::InProgress]);
    TaskModel::factory()->count(2)->create(['project_id' => $project->id, 'status' => TaskStatus::Completed]);
    TaskModel::factory()->create(['project_id' => ProjectModel::factory()->create()->id, 'status' => TaskStatus::Open]);

    $this->actingAs($this->user)
        ->getJson('/api/projects/pinned')
        ->assertOk()
        ->assertJsonPath('data.0.task_status_counts', [
            'backlog'               => 0,
            'open'                  => 3,
            'ready_for_development' => 0,
            'in_progress'           => 1,
            'ready_to_test'         => 0,
            'completed'             => 2,
            'closed'                => 0,
            'declined'              => 0,
        ])
        ->assertJsonPath('data.0.tasks_count', 6);
});

it('counts statuses for every pinned project with a single query', function () {
    $projects = ProjectModel::factory()->count(3)->create();
    foreach ($projects as $project) {
        $this->user->pinnedProjects()->attach($project->id, ['created_at' => now()]);
        TaskModel::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::Backlog]);
    }

    $this->actingAs($this->user);

    DB::enableQueryLog();
    $response = $this->getJson('/api/projects/pinned')->assertOk()->assertJsonCount(3, 'data');
    $countQueries = array_filter(DB::getQueryLog(), fn (array $q) => str_contains($q['query'], 'group by "project_id", "status"'));
    DB::disableQueryLog();

    expect($countQueries)->toHaveCount(1);
    foreach ([0, 1, 2] as $i) {
        $response->assertJsonPath("data.{$i}.task_status_counts.backlog", 1);
    }
});

it('keeps pinned lists private to each user', function () {
    $other = UserModel::factory()->create();
    [$mine, $theirs] = ProjectModel::factory()->count(2)->create();

    $this->user->pinnedProjects()->attach($mine->id, ['created_at' => now()]);
    $other->pinnedProjects()->attach($theirs->id, ['created_at' => now()]);

    $this->actingAs($this->user)
        ->getJson('/api/projects/pinned')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $mine->id);
});

it('rejects unauthenticated requests', function () {
    $this->getJson('/api/projects/pinned')->assertUnauthorized();
});
