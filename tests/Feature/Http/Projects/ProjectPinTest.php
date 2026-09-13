<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->project = ProjectModel::factory()->create();
});

it('reports the project as not pinned before pinning', function () {
    $this->actingAs($this->user)
        ->getJson("/api/projects/{$this->project->id}")
        ->assertOk()
        ->assertJsonPath('data.is_pinned', false);
});

it('pins a project for the current user and reports it in show, index and search', function () {
    $this->actingAs($this->user)
        ->postJson("/api/projects/{$this->project->id}/pin")
        ->assertNoContent();

    $this->getJson("/api/projects/{$this->project->id}")
        ->assertOk()
        ->assertJsonPath('data.is_pinned', true);

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonPath('data.0.is_pinned', true);

    $this->postJson('/api/projects/search', ['query' => ''])
        ->assertOk()
        ->assertJsonPath('data.0.is_pinned', true);
});

it('keeps a single row when the project is pinned twice', function () {
    $this->actingAs($this->user);

    $this->postJson("/api/projects/{$this->project->id}/pin")->assertNoContent();
    $this->postJson("/api/projects/{$this->project->id}/pin")->assertNoContent();

    $this->assertDatabaseCount('user_pinned_projects', 1);
});

it('unpins a project', function () {
    $this->actingAs($this->user);
    $this->postJson("/api/projects/{$this->project->id}/pin")->assertNoContent();

    $this->deleteJson("/api/projects/{$this->project->id}/pin")->assertNoContent();

    $this->assertDatabaseCount('user_pinned_projects', 0);
    $this->getJson("/api/projects/{$this->project->id}")
        ->assertJsonPath('data.is_pinned', false);
});

it('unpinning a project that is not pinned succeeds', function () {
    $this->actingAs($this->user)
        ->deleteJson("/api/projects/{$this->project->id}/pin")
        ->assertNoContent();
});

it('keeps pins private to the user who made them', function () {
    $other = UserModel::factory()->create();

    $this->actingAs($this->user)
        ->postJson("/api/projects/{$this->project->id}/pin")
        ->assertNoContent();

    $this->actingAs($other)
        ->getJson("/api/projects/{$this->project->id}")
        ->assertJsonPath('data.is_pinned', false);
    $this->getJson('/api/projects')
        ->assertJsonPath('data.0.is_pinned', false);
});

it('removes pins when the project is deleted', function () {
    $this->actingAs($this->user);
    $this->postJson("/api/projects/{$this->project->id}/pin")->assertNoContent();

    $this->deleteJson("/api/projects/{$this->project->id}")->assertOk();

    $this->assertDatabaseCount('user_pinned_projects', 0);
});

it('rejects unauthenticated pin requests', function () {
    $this->postJson("/api/projects/{$this->project->id}/pin")->assertUnauthorized();
    $this->deleteJson("/api/projects/{$this->project->id}/pin")->assertUnauthorized();
});

it('leaves is_pinned out of the CLI API project payload', function () {
    $this->actingAs($this->user);
    $this->postJson("/api/projects/{$this->project->id}/pin")->assertNoContent();

    $this->getJson("/api/cli/projects/{$this->project->id}")
        ->assertOk()
        ->assertJsonMissingPath('data.is_pinned');
});
