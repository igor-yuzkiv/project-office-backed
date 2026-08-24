<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
});

it('returns the project icon', function () {
    $project = ProjectModel::factory()->create(['icon' => 'tabler:rocket']);

    $this->getJson("/api/cli/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.icon', 'tabler:rocket');
});

it('returns null for a project without an icon', function () {
    $project = ProjectModel::factory()->create(['icon' => null]);

    $this->getJson("/api/cli/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.icon', null);
});
