<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sets a root document path to its own id and depth to zero', function () {
    $project = ProjectModel::factory()->create();

    $document = ProjectDocumentModel::factory()->for($project, 'project')->create();

    expect($document->path)->toBe($document->id)
        ->and($document->depth)->toBe(0)
        ->and($document->status)->toBe(ProjectDocumentStatus::Draft);
});

it('builds the path and depth from the parent document', function () {
    $project = ProjectModel::factory()->create();

    $root = ProjectDocumentModel::factory()->for($project, 'project')->create();
    $child = ProjectDocumentModel::factory()->for($project, 'project')->create(['parent_id' => $root->id]);
    $grandchild = ProjectDocumentModel::factory()->for($project, 'project')->create(['parent_id' => $child->id]);

    expect($child->path)->toBe("{$root->id}.{$child->id}")
        ->and($child->depth)->toBe(1)
        ->and($grandchild->path)->toBe("{$root->id}.{$child->id}.{$grandchild->id}")
        ->and($grandchild->depth)->toBe(2);

    expect($root->children->pluck('id'))->toEqual(collect([$child->id]));
});

it('rejects creating a document beyond the maximum nesting depth', function () {
    $project = ProjectModel::factory()->create();

    $root = ProjectDocumentModel::factory()->for($project, 'project')->create();
    $child = ProjectDocumentModel::factory()->for($project, 'project')->create(['parent_id' => $root->id]);
    $grandchild = ProjectDocumentModel::factory()->for($project, 'project')->create(['parent_id' => $child->id]);

    expect($grandchild->depth)->toBe(ProjectDocumentModel::MAX_DEPTH);

    ProjectDocumentModel::factory()->for($project, 'project')->create(['parent_id' => $grandchild->id]);
})->throws(DomainException::class);

it('rejects a child document whose parent belongs to a different project', function () {
    $projectA = ProjectModel::factory()->create();
    $projectB = ProjectModel::factory()->create();

    $root = ProjectDocumentModel::factory()->for($projectA, 'project')->create();

    ProjectDocumentModel::factory()->for($projectB, 'project')->create(['parent_id' => $root->id]);
})->throws(DomainException::class);

it('rejects a document being its own parent', function () {
    $project = ProjectModel::factory()->create();
    $document = ProjectDocumentModel::factory()->for($project, 'project')->create();

    $document->parent_id = $document->id;
    $document->save();
})->throws(DomainException::class);

it('rejects moving a document under its own descendant', function () {
    $project = ProjectModel::factory()->create();

    $root = ProjectDocumentModel::factory()->for($project, 'project')->create();
    $child = ProjectDocumentModel::factory()->for($project, 'project')->create(['parent_id' => $root->id]);

    $root->parent_id = $child->id;
    $root->save();
})->throws(DomainException::class);

it('rejects reassigning a child document to a different project without moving it out from under its parent', function () {
    $projectA = ProjectModel::factory()->create();
    $projectB = ProjectModel::factory()->create();

    $root = ProjectDocumentModel::factory()->for($projectA, 'project')->create();
    $child = ProjectDocumentModel::factory()->for($projectA, 'project')->create(['parent_id' => $root->id]);

    $child->project_id = $projectB->id;
    $child->save();
})->throws(DomainException::class);

it('enforces unique titles among siblings including root level', function () {
    $project = ProjectModel::factory()->create();

    ProjectDocumentModel::factory()->for($project, 'project')->create(['title' => 'Duplicate']);
    ProjectDocumentModel::factory()->for($project, 'project')->create(['title' => 'Duplicate']);
})->throws(QueryException::class);

it('links a document to tasks of the same project', function () {
    $project = ProjectModel::factory()->create();
    $document = ProjectDocumentModel::factory()->for($project, 'project')->create();
    $task = TaskModel::factory()->for($project, 'project')->create();

    $document->tasks()->attach($task);

    expect($document->tasks()->pluck('tasks.id'))->toEqual(collect([$task->id]));
});
