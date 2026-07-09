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
    $this->task = TaskModel::factory()->for($this->project, 'project')->create();
});

it('returns paginated project documents linked to the task', function () {
    $documents = ProjectDocumentModel::factory()->count(3)->create(['project_id' => $this->project->id]);
    $this->task->projectDocuments()->attach($documents);

    $response = $this->getJson("/api/tasks/{$this->task->id}/project-documents");

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);

    expect($response->json('meta.total'))->toBe(3);
});

it('does not return project documents linked to other tasks', function () {
    $otherTask = TaskModel::factory()->for($this->project, 'project')->create();
    ProjectDocumentModel::factory()->create(['project_id' => $this->project->id])
        ->tasks()->attach($otherTask);

    $response = $this->getJson("/api/tasks/{$this->task->id}/project-documents");

    $response->assertOk();

    expect($response->json('meta.total'))->toBe(0);
});
