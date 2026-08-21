<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
});

it('returns the project emoji', function () {
    $project = ProjectModel::factory()->create(['icon_emoji' => '🚀']);

    $this->getJson("/api/cli/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.icon_emoji', '🚀');
});

it('returns null for a project without an emoji', function () {
    $project = ProjectModel::factory()->create(['icon_emoji' => null]);

    $this->getJson("/api/cli/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.icon_emoji', null);
});
