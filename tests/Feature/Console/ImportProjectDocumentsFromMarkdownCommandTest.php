<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->project = ProjectModel::factory()->create();
    $this->root = sys_get_temp_dir().'/pd-import-test-'.uniqid();
    File::makeDirectory($this->root.'/Guides', recursive: true);

    File::put($this->root.'/Overview.md', "# Overview\n\n![diagram](https://example.com/diagram.png)\n\nSome text.");
    File::put($this->root.'/Guides/Getting Started.md', "# Getting Started\n\nStep one.");
});

afterEach(function () {
    File::deleteDirectory($this->root);
});

it('imports markdown files mirroring the folder structure, stripping images', function () {
    $this->artisan('project-documents:import', [
        'project' => $this->project->id,
        'path'    => $this->root,
    ])->assertSuccessful();

    $overview = ProjectDocumentModel::where('project_id', $this->project->id)
        ->where('title', 'Overview')
        ->firstOrFail();

    expect($overview->parent_id)->toBeNull();
    expect($overview->content)->not->toContain('example.com/diagram.png');
    expect($overview->content)->toContain('Some text.');

    $guidesFolder = ProjectDocumentModel::where('project_id', $this->project->id)
        ->where('title', 'Guides')
        ->firstOrFail();

    expect($guidesFolder->parent_id)->toBeNull();
    expect($guidesFolder->content)->toBeNull();

    $gettingStarted = ProjectDocumentModel::where('project_id', $this->project->id)
        ->where('title', 'Getting Started')
        ->firstOrFail();

    expect($gettingStarted->parent_id)->toBe($guidesFolder->id);
    expect($gettingStarted->content)->toContain('Step one.');
});

it('skips a duplicate title under the same parent instead of failing', function () {
    ProjectDocumentModel::factory()->create([
        'project_id' => $this->project->id,
        'title'      => 'Overview',
    ]);

    $this->artisan('project-documents:import', [
        'project' => $this->project->id,
        'path'    => $this->root,
    ])->assertSuccessful();

    expect(ProjectDocumentModel::where('project_id', $this->project->id)->where('title', 'Overview')->count())->toBe(1);
});

it('fails gracefully for a non-existent project', function () {
    $this->artisan('project-documents:import', [
        'project' => (string) Str::ulid(),
        'path'    => $this->root,
    ])->assertFailed();
});
