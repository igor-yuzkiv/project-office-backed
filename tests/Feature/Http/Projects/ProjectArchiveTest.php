<?php

use App\Domains\Project\Enums\ProjectStatus;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
});

it('sets archived_at and archived_by when status changes to archived', function () {
    $project = ProjectModel::factory()->create(['status' => ProjectStatus::ACTIVE]);

    $this->putJson("/api/projects/{$project->id}", ['status' => 'archived'])
        ->assertOk();

    $project->refresh();

    expect($project->status)->toBe(ProjectStatus::ARCHIVED)
        ->and($project->archived_at)->not->toBeNull()
        ->and($project->archived_by)->toBe($this->user->id);
});

it('clears archived_at and archived_by when status changes from archived to another', function () {
    $project = ProjectModel::factory()->create([
        'status'      => ProjectStatus::ARCHIVED,
        'archived_at' => now(),
        'archived_by' => $this->user->id,
    ]);

    $this->putJson("/api/projects/{$project->id}", ['status' => 'active'])
        ->assertOk();

    $project->refresh();

    expect($project->status)->toBe(ProjectStatus::ACTIVE)
        ->and($project->archived_at)->toBeNull()
        ->and($project->archived_by)->toBeNull();
});

it('does not change archived columns when status remains non-archived', function () {
    $project = ProjectModel::factory()->create(['status' => ProjectStatus::ACTIVE]);

    $this->putJson("/api/projects/{$project->id}", ['name' => 'Updated Name'])
        ->assertOk();

    $project->refresh();

    expect($project->archived_at)->toBeNull()
        ->and($project->archived_by)->toBeNull();
});
