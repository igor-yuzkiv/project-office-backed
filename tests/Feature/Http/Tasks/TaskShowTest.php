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
});

it('does not include related project documents by default', function () {
    $task = TaskModel::factory()->for($this->project, 'project')->create();
    $document = ProjectDocumentModel::factory()->create(['project_id' => $this->project->id]);
    $task->projectDocuments()->attach($document);

    $response = $this->getJson('/api/tasks/'.$task->id);

    $response->assertOk()->assertJsonMissingPath('data.project_documents');
});

it('includes related project documents when explicitly requested via include', function () {
    $task = TaskModel::factory()->for($this->project, 'project')->create();
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Linked Document',
    ]);
    $task->projectDocuments()->attach($document);

    $response = $this->getJson('/api/tasks/'.$task->id.'?include=projectDocuments');

    $response->assertOk()
        ->assertJsonPath('data.id', $task->id)
        ->assertJsonPath('data.project_documents.0.id', $document->id)
        ->assertJsonPath('data.project_documents.0.title', 'Linked Document');
});
