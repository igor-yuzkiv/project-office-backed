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
    $this->taskList = TaskListModel::factory()->create([
        'project_id'      => $this->project->id,
        'key'             => 'MTM-TL-7',
        'sequence_number' => 7,
        'name'            => 'Original name',
        'status'          => TaskListStatus::Open->value,
        'description'     => 'Original description',
    ]);
});

it('updates the name, status and description by key', function () {
    $response = $this->putJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7", [
        'name'        => 'Renamed list',
        'status'      => TaskListStatus::Completed->value,
        'description' => 'Updated description',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Renamed list')
        ->assertJsonPath('data.status', TaskListStatus::Completed->value)
        ->assertJsonPath('data.description', 'Updated description');

    $fresh = $this->taskList->fresh();
    expect($fresh->name)->toBe('Renamed list')
        ->and($fresh->status)->toBe(TaskListStatus::Completed);
});

it('keeps the fields that are absent from the payload', function () {
    $response = $this->putJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7", [
        'status' => TaskListStatus::Completed->value,
    ]);

    $response->assertOk();

    $fresh = $this->taskList->fresh();
    expect($fresh->name)->toBe('Original name')
        ->and($fresh->description)->toBe('Original description');
});

it('replaces tags from a comma-separated string and leaves them alone when absent', function () {
    $existing = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $this->taskList->tags()->attach([$existing->id]);

    $this->putJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7", [
        'tags' => 'alpha,beta',
    ])->assertOk();

    expect($this->taskList->fresh()->tags()->pluck('name')->sort()->values()->all())->toBe(['alpha', 'beta']);

    $this->putJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7", [
        'name' => 'Renamed list',
    ])->assertOk();

    expect($this->taskList->fresh()->tags()->count())->toBe(2);
});

it('clears tags when the tags string is empty', function () {
    $existing = TagModel::create(['name' => 'alpha', 'color' => '#111111']);
    $this->taskList->tags()->attach([$existing->id]);

    $this->putJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7", [
        'tags' => '',
    ])->assertOk();

    expect($this->taskList->fresh()->tags()->count())->toBe(0);
});

it('does not update a task list of another project', function () {
    $otherProject = ProjectModel::factory()->create(['prefix' => 'OTH']);
    TaskListModel::factory()->create([
        'project_id'      => $otherProject->id,
        'key'             => 'OTH-TL-1',
        'sequence_number' => 1,
    ]);

    $this->putJson("/api/cli/projects/{$this->project->id}/task-lists/OTH-TL-1", [
        'name' => 'Hijacked',
    ])->assertNotFound();
});
