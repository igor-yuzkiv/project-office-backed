<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\TaskList\Services\TaskListKeyResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('starts numbering at one for a project without task lists', function () {
    $project = ProjectModel::factory()->create(['prefix' => 'MTM']);

    $key = app(TaskListKeyResolver::class)->resolve($project);

    expect($key->value)->toBe('MTM-TL-1')
        ->and($key->sequenceNumber)->toBe(1);
});

it('continues numbering from the highest sequence number in the project', function () {
    $project = ProjectModel::factory()->create(['prefix' => 'MTM']);
    TaskListModel::factory()->create([
        'project_id'      => $project->id,
        'key'             => 'MTM-TL-1',
        'sequence_number' => 1,
    ]);

    $key = app(TaskListKeyResolver::class)->resolve($project);

    expect($key->value)->toBe('MTM-TL-2')
        ->and($key->sequenceNumber)->toBe(2);
});

it('numbers task lists per project', function () {
    $project = ProjectModel::factory()->create(['prefix' => 'MTM']);
    $otherProject = ProjectModel::factory()->create(['prefix' => 'OTH']);
    TaskListModel::factory()->create([
        'project_id'      => $project->id,
        'key'             => 'MTM-TL-1',
        'sequence_number' => 1,
    ]);

    $key = app(TaskListKeyResolver::class)->resolve($otherProject);

    expect($key->value)->toBe('OTH-TL-1');
});
