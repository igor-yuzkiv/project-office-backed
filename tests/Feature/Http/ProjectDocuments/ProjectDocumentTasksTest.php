<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
    $this->document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
});

it('returns paginated tasks linked to the document', function () {
    $tasks = TaskModel::factory()->count(3)->create(['project_id' => $this->project->id]);
    $this->document->tasks()->sync($tasks->pluck('id')->all());

    $response = $this->getJson("/api/project-documents/{$this->document->id}/tasks");

    $response->assertOk()->assertJsonStructure(['data', 'meta', 'links']);

    expect($response->json('meta.total'))->toBe(3);
});

it('does not return tasks linked to other documents', function () {
    $otherDocument = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $otherDocument->tasks()->sync([TaskModel::factory()->create(['project_id' => $this->project->id])->id]);

    $response = $this->getJson("/api/project-documents/{$this->document->id}/tasks");

    $response->assertOk();

    expect($response->json('meta.total'))->toBe(0);
});

it('attaches tasks to the document', function () {
    $tasks = TaskModel::factory()->count(2)->create(['project_id' => $this->project->id]);

    $response = $this->putJson("/api/project-documents/{$this->document->id}/tasks", [
        'task_ids' => $tasks->pluck('id')->all(),
    ]);

    $response->assertOk()
        ->assertJsonStructure(['data'])
        ->assertJsonCount(2, 'data');

    expect($this->document->tasks()->count())->toBe(2);
});

it('replaces previously attached tasks on subsequent sync', function () {
    $initialTasks = TaskModel::factory()->count(2)->create(['project_id' => $this->project->id]);
    $this->document->tasks()->sync($initialTasks->pluck('id')->all());

    $newTask = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->putJson("/api/project-documents/{$this->document->id}/tasks", [
        'task_ids' => [$newTask->id],
    ]);

    $response->assertOk()->assertJsonCount(1, 'data');

    $attachedIds = $this->document->tasks()->pluck('tasks.id')->all();

    expect($attachedIds)->toBe([$newTask->id]);
});

it('rejects a task belonging to a different project', function () {
    $otherProject = ProjectModel::factory()->create();
    $foreignTask = TaskModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->putJson("/api/project-documents/{$this->document->id}/tasks", [
        'task_ids' => [$foreignTask->id],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('task_ids.0');

    expect($this->document->tasks()->count())->toBe(0);
});

it('rejects a non-existent task id', function () {
    $response = $this->putJson("/api/project-documents/{$this->document->id}/tasks", [
        'task_ids' => ['non-existent-task-id'],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('task_ids.0');
});
