<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create(['prefix' => 'MTM']);
});

it('creates a task list with a generated key', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/task-lists", [
        'name'        => 'Release plan',
        'status'      => TaskListStatus::InProgress->value,
        'description' => 'What ships this week',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.key', 'MTM-TL-1')
        ->assertJsonPath('data.name', 'Release plan')
        ->assertJsonPath('data.status', TaskListStatus::InProgress->value)
        ->assertJsonPath('data.description', 'What ships this week');

    expect(TaskListModel::where('project_id', $this->project->id)->count())->toBe(1);
});

it('defaults the status to open', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/task-lists", [
        'name' => 'Backlog grooming',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', TaskListStatus::Open->value);
});

it('creates tags from a comma-separated string and reuses existing ones', function () {
    $existing = TagModel::create(['name' => 'alpha', 'color' => '#111111']);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/task-lists", [
        'name' => 'Tagged list',
        'tags' => 'Alpha, beta',
    ]);

    $response->assertCreated();

    $taskList = TaskListModel::firstOrFail();
    $tagIds = $taskList->tags()->pluck('tags.id')->all();

    expect($tagIds)->toContain($existing->id)
        ->and($taskList->tags()->count())->toBe(2)
        ->and(TagModel::where('name', 'alpha')->count())->toBe(1);
});

it('rejects a request without a name', function () {
    $this->postJson("/api/cli/projects/{$this->project->id}/task-lists", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('rejects an unknown status', function () {
    $this->postJson("/api/cli/projects/{$this->project->id}/task-lists", [
        'name'   => 'Release plan',
        'status' => 'archived',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});
