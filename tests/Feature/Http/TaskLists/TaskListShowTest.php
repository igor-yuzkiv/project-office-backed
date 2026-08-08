<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create(['prefix' => 'MTM']);
});

it('returns description, status, tags and metadata', function () {
    $tag = TagModel::factory()->create();
    $taskList = TaskListModel::factory()->create([
        'project_id'  => $this->project->id,
        'key'         => 'MTM-TL-3',
        'name'        => 'Sprint 4',
        'status'      => TaskListStatus::InProgress->value,
        'description' => '# Notes',
    ]);
    $taskList->tags()->sync([$tag->id]);
    TaskModel::factory()->count(2)->create([
        'project_id'   => $this->project->id,
        'task_list_id' => $taskList->id,
    ]);

    $response = $this->getJson("/api/task-lists/{$taskList->id}");

    $response->assertOk()
        ->assertJsonPath('data.key', 'MTM-TL-3')
        ->assertJsonPath('data.status', 'in_progress')
        ->assertJsonPath('data.description', '# Notes')
        ->assertJsonMissingPath('data.tasks_count')
        ->assertJsonPath('data.tags.0.id', $tag->id)
        ->assertJsonPath('data.project.id', $this->project->id);
});

it('accepts status, description and tags on create', function () {
    $tag = TagModel::factory()->create();

    $response = $this->postJson('/api/task-lists', [
        'project_id'  => $this->project->id,
        'name'        => 'Sprint 5',
        'status'      => 'completed',
        'description' => '# Plan',
        'tag_ids'     => [$tag->id],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.description', '# Plan')
        ->assertJsonPath('data.tags.0.id', $tag->id);
});

it('accepts status, description and tags on update', function () {
    $tag = TagModel::factory()->create();
    $taskList = TaskListModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->putJson("/api/task-lists/{$taskList->id}", [
        'status'      => 'completed',
        'description' => '# Done',
        'tag_ids'     => [$tag->id],
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.description', '# Done')
        ->assertJsonPath('data.tags.0.id', $tag->id);
});

it('clears the description when null is sent', function () {
    $taskList = TaskListModel::factory()->create([
        'project_id'  => $this->project->id,
        'description' => '# Original',
    ]);

    $this->putJson("/api/task-lists/{$taskList->id}", ['description' => null])
        ->assertOk()
        ->assertJsonPath('data.description', null);
});

it('rejects an unknown status', function () {
    $taskList = TaskListModel::factory()->create(['project_id' => $this->project->id]);

    $this->putJson("/api/task-lists/{$taskList->id}", ['status' => 'archived'])
        ->assertStatus(422);
});
