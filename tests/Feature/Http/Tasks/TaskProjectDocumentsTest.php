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

it('attaches project documents to the task', function () {
    $documents = ProjectDocumentModel::factory()->count(2)->create(['project_id' => $this->project->id]);

    $response = $this->putJson("/api/tasks/{$this->task->id}/project-documents", [
        'document_ids' => $documents->pluck('id')->all(),
    ]);

    $response->assertOk()
        ->assertJsonStructure(['data'])
        ->assertJsonCount(2, 'data');

    expect($this->task->projectDocuments()->count())->toBe(2);
});

it('replaces previously attached project documents on subsequent sync', function () {
    $initialDocuments = ProjectDocumentModel::factory()->count(2)->create(['project_id' => $this->project->id]);
    $this->task->projectDocuments()->sync($initialDocuments->pluck('id')->all());

    $newDocument = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->putJson("/api/tasks/{$this->task->id}/project-documents", [
        'document_ids' => [$newDocument->id],
    ]);

    $response->assertOk()->assertJsonCount(1, 'data');

    $attachedIds = $this->task->projectDocuments()->pluck('project_documents.id')->all();

    expect($attachedIds)->toBe([$newDocument->id]);
});

it('rejects a project document belonging to a different project', function () {
    $otherProject = ProjectModel::factory()->create();
    $foreignDocument = ProjectDocumentModel::factory()->create(['project_id' => $otherProject->id]);

    $response = $this->putJson("/api/tasks/{$this->task->id}/project-documents", [
        'document_ids' => [$foreignDocument->id],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('document_ids.0');

    expect($this->task->projectDocuments()->count())->toBe(0);
});

it('rejects a non-existent project document id', function () {
    $response = $this->putJson("/api/tasks/{$this->task->id}/project-documents", [
        'document_ids' => ['non-existent-document-id'],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('document_ids.0');
});
