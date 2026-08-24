<?php

use App\Domains\Project\Enums\ProjectStatus;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
});

it('records a project.created event on creation', function () {
    $this->postJson('/api/projects', [
        'name'   => 'MVP Task Manager',
        'prefix' => 'MTM',
    ])->assertCreated();

    $project = ProjectModel::query()->sole();
    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('project.created')
        ->and($record->title)->toBe("{$this->user->name} created project «MVP Task Manager»")
        ->and($record->description)->toBe('MTM')
        ->and($record->subject_type)->toBe(ProjectModel::class)
        ->and($record->subject_id)->toBe($project->id);
});

it('names the changed fields on update', function () {
    $project = ProjectModel::factory()->create([
        'name'   => 'Sandbox',
        'status' => ProjectStatus::DRAFT->value,
    ]);

    $this->putJson("/api/projects/{$project->id}", [
        'name'        => 'Renamed',
        'status'      => $project->status->value,
        'description' => $project->description,
        'start_date'  => $project->start_date?->toDateString(),
        'end_date'    => $project->end_date?->toDateString(),
    ])->assertOk();

    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('project.updated')
        ->and($record->title)->toBe("{$this->user->name} updated project «Renamed»")
        ->and($record->description)->toBe('Changed name');
});

it('records nothing when an update changes no field', function () {
    $project = ProjectModel::factory()->create(['name' => 'Sandbox']);

    $this->putJson("/api/projects/{$project->id}", [
        'name'        => 'Sandbox',
        'status'      => $project->status->value,
        'description' => $project->description,
        'start_date'  => $project->start_date?->toDateString(),
        'end_date'    => $project->end_date?->toDateString(),
    ])->assertOk();

    expect(AuditRecordModel::query()->count())->toBe(0);
});

it('records a project.deleted event that keeps pointing at the deleted project', function () {
    $project = ProjectModel::factory()->create(['name' => 'Sandbox']);

    $this->deleteJson("/api/projects/{$project->id}")->assertOk();

    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('project.deleted')
        ->and($record->title)->toBe("{$this->user->name} deleted project «Sandbox»")
        ->and($record->description)->toBeNull()
        ->and($record->subject_type)->toBe(ProjectModel::class)
        ->and($record->subject_id)->toBe($project->id);
});

it('creates a project with an icon and without one', function () {
    $this->postJson('/api/projects', [
        'name'   => 'With icon',
        'prefix' => 'WIC',
        'icon'   => 'tabler:rocket',
    ])->assertCreated()->assertJsonPath('data.icon', 'tabler:rocket');

    $this->postJson('/api/projects', [
        'name'   => 'Without icon',
        'prefix' => 'WOI',
    ])->assertCreated()->assertJsonPath('data.icon', null);
});

it('sets and resets the project icon on update', function () {
    $project = ProjectModel::factory()->create(['icon' => null]);

    $payload = [
        'name'   => $project->name,
        'status' => $project->status->value,
    ];

    $this->putJson("/api/projects/{$project->id}", [...$payload, 'icon' => 'tabler:book'])
        ->assertOk()
        ->assertJsonPath('data.icon', 'tabler:book');

    $this->putJson("/api/projects/{$project->id}", [...$payload, 'icon' => null])
        ->assertOk()
        ->assertJsonPath('data.icon', null);

    expect($project->fresh()->icon)->toBeNull();
});

// The picker also accepts a name typed by hand, from any icon set, so the field must not be
// fussier than the catalogue is.
it('accepts an icon name from any set', function ($icon) {
    $response = $this->postJson('/api/projects', [
        'name'   => 'Any set',
        'prefix' => 'ANY',
        'icon'   => $icon,
    ])->assertCreated();

    expect($response->json('data.icon'))->toBe($icon)
        ->and(ProjectModel::query()->sole()->icon)->toBe($icon);
})->with([
    'tabler'    => 'tabler:rocket',
    'heroicons' => 'heroicons:document-text',
    'material'  => 'material-symbols:folder-outline',
]);

it('rejects an icon name longer than 64 characters', function () {
    $this->postJson('/api/projects', [
        'name'   => 'Too long',
        'prefix' => 'TOL',
        'icon'   => 'tabler:'.str_repeat('a', 58),
    ])->assertJsonValidationErrors('icon');

    $project = ProjectModel::factory()->create();

    $this->putJson("/api/projects/{$project->id}", [
        'name'   => $project->name,
        'status' => $project->status->value,
        'icon'   => str_repeat('a', 65),
    ])->assertJsonValidationErrors('icon');
});

// The project card shows what a project holds, and it reads those numbers off the list itself
// rather than asking three more times per card.
it('counts documents, task lists and tasks on both list endpoints', function () {
    $project = ProjectModel::factory()->create();

    ProjectDocumentModel::factory()->count(2)->create(['project_id' => $project->id]);
    TaskListModel::factory()->count(3)->create(['project_id' => $project->id]);
    TaskModel::factory()->count(4)->for($project, 'project')->create();

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonPath('data.0.docs_count', 2)
        ->assertJsonPath('data.0.task_lists_count', 3)
        ->assertJsonPath('data.0.tasks_count', 4);

    $this->postJson('/api/projects/search', ['query' => ''])
        ->assertOk()
        ->assertJsonPath('data.0.docs_count', 2)
        ->assertJsonPath('data.0.task_lists_count', 3)
        ->assertJsonPath('data.0.tasks_count', 4);
});

it('counts an empty project as zero rather than omitting the counts', function () {
    ProjectModel::factory()->create();

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonPath('data.0.docs_count', 0)
        ->assertJsonPath('data.0.task_lists_count', 0)
        ->assertJsonPath('data.0.tasks_count', 0);
});

it('returns the icon from the project list and detail endpoints', function () {
    $project = ProjectModel::factory()->create(['icon' => 'tabler:bug']);

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonPath('data.0.icon', 'tabler:bug');

    $this->getJson("/api/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.icon', 'tabler:bug');
});
