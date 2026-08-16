<?php

use App\Domains\Project\Enums\ProjectStatus;
use App\Domains\Project\Models\ProjectModel;
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
