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

it('creates a project with an emoji and without one', function () {
    $this->postJson('/api/projects', [
        'name'       => 'With icon',
        'prefix'     => 'WIC',
        'icon_emoji' => '🚀',
    ])->assertCreated()->assertJsonPath('data.icon_emoji', '🚀');

    $this->postJson('/api/projects', [
        'name'   => 'Without icon',
        'prefix' => 'WOI',
    ])->assertCreated()->assertJsonPath('data.icon_emoji', null);
});

it('sets and resets the project emoji on update', function () {
    $project = ProjectModel::factory()->create(['icon_emoji' => null]);

    $payload = [
        'name'   => $project->name,
        'status' => $project->status->value,
    ];

    $this->putJson("/api/projects/{$project->id}", [...$payload, 'icon_emoji' => '📚'])
        ->assertOk()
        ->assertJsonPath('data.icon_emoji', '📚');

    $this->putJson("/api/projects/{$project->id}", [...$payload, 'icon_emoji' => null])
        ->assertOk()
        ->assertJsonPath('data.icon_emoji', null);

    expect($project->fresh()->icon_emoji)->toBeNull();
});

it('keeps composed emoji sequences intact', function ($emoji) {
    $response = $this->postJson('/api/projects', [
        'name'       => 'Composed',
        'prefix'     => 'CMP',
        'icon_emoji' => $emoji,
    ])->assertCreated();

    expect($response->json('data.icon_emoji'))->toBe($emoji)
        ->and(ProjectModel::query()->sole()->icon_emoji)->toBe($emoji);
})->with([
    'flag'       => '🇺🇦',
    'zwj family' => '👨‍👩‍👧‍👦',
    'skin tone'  => '👍🏽',
]);

it('rejects an emoji longer than 32 characters', function () {
    $this->postJson('/api/projects', [
        'name'       => 'Too long',
        'prefix'     => 'TOL',
        'icon_emoji' => str_repeat('🙂', 33),
    ])->assertJsonValidationErrors('icon_emoji');

    $project = ProjectModel::factory()->create();

    $this->putJson("/api/projects/{$project->id}", [
        'name'       => $project->name,
        'status'     => $project->status->value,
        'icon_emoji' => str_repeat('a', 33),
    ])->assertJsonValidationErrors('icon_emoji');
});

it('returns icon_emoji from the project list and detail endpoints', function () {
    $project = ProjectModel::factory()->create(['icon_emoji' => '🐛']);

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonPath('data.0.icon_emoji', '🐛');

    $this->getJson("/api/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.icon_emoji', '🐛');
});
