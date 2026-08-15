<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
    $this->task = TaskModel::factory()->create([
        'project_id'  => $this->project->id,
        'name'        => 'Original',
        'description' => 'Original body.',
        'status'      => TaskStatus::Open->value,
        'priority'    => TaskPriority::High->value,
        // Set explicitly: the factory leaves dates null most of the time, which would make the
        // clearing assertions below pass without exercising anything.
        'start_date' => '2026-01-01',
        'due_date'   => '2026-01-31',
    ]);
});

/**
 * The edit form always submits every field, so a payload here is a whole task.
 */
function taskUpdatePayload(array $overrides = []): array
{
    return array_merge([
        'task_list_id' => null,
        'name'         => 'Updated',
        'description'  => 'Updated body.',
        'priority'     => TaskPriority::Low->value,
        'status'       => TaskStatus::InProgress->value,
        'start_date'   => null,
        'due_date'     => null,
        'tag_ids'      => [],
    ], $overrides);
}

it('updates every field the edit form submits', function () {
    $tag = TagModel::factory()->create();

    $this->putJson("/api/tasks/{$this->task->id}", taskUpdatePayload(['tag_ids' => [$tag->id]]))
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated')
        ->assertJsonPath('data.description', 'Updated body.')
        ->assertJsonPath('data.status', TaskStatus::InProgress->value)
        ->assertJsonPath('data.priority.value', TaskPriority::Low->value);

    expect($this->task->fresh()->tags->pluck('id')->all())->toBe([$tag->id]);
});

it('clears description, dates and tags when they are sent as empty', function () {
    $tag = TagModel::factory()->create();
    $this->task->tags()->sync([$tag->id]);

    $this->putJson("/api/tasks/{$this->task->id}", taskUpdatePayload(['description' => null]))
        ->assertOk()
        ->assertJsonPath('data.description', null)
        ->assertJsonPath('data.start_date', null)
        ->assertJsonPath('data.due_date', null);

    expect($this->task->fresh()->tags)->toBeEmpty();
});

it('falls back to the None priority when priority is null', function () {
    $this->putJson("/api/tasks/{$this->task->id}", taskUpdatePayload(['priority' => null]))
        ->assertOk()
        ->assertJsonPath('data.priority.value', TaskPriority::None->value);
});

it('requires name and status', function () {
    $payload = taskUpdatePayload();
    unset($payload['name'], $payload['status']);

    $this->putJson("/api/tasks/{$this->task->id}", $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'status']);
});

it('records task.updated with the changed fields when the status stays the same', function () {
    $this->putJson("/api/tasks/{$this->task->id}", taskUpdatePayload([
        'status'   => TaskStatus::Open->value,
        'priority' => TaskPriority::High->value,
    ]))->assertOk();

    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('task.updated')
        ->and($record->subject_type)->toBe(TaskModel::class)
        ->and($record->subject_id)->toBe($this->task->id)
        ->and($record->title)->toEndWith("updated {$this->task->key}")
        ->and($record->description)->toBe('Changed name, description, start date and due date');
});

it('names the single changed field when only one moves', function () {
    $this->putJson("/api/tasks/{$this->task->id}", taskUpdatePayload([
        'description' => 'Original body.',
        'status'      => TaskStatus::Open->value,
        'priority'    => TaskPriority::High->value,
        'start_date'  => '2026-01-01',
        'due_date'    => '2026-01-31',
    ]))->assertOk();

    expect(AuditRecordModel::query()->sole()->description)->toBe('Changed name');
});

it('records only task.status_changed when nothing but the status moves', function () {
    $this->putJson("/api/tasks/{$this->task->id}", taskUpdatePayload([
        'name'        => 'Original',
        'description' => 'Original body.',
        'priority'    => TaskPriority::High->value,
        'start_date'  => '2026-01-01',
        'due_date'    => '2026-01-31',
    ]))->assertOk();

    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('task.status_changed')
        ->and($record->title)->toEndWith("moved {$this->task->key} to In progress")
        ->and($record->description)->toBe('Open → In progress');
});

it('records both events when the status and other fields change together', function () {
    $this->putJson("/api/tasks/{$this->task->id}", taskUpdatePayload())->assertOk();

    expect(AuditRecordModel::query()->orderBy('id')->pluck('type')->all())
        ->toBe(['task.status_changed', 'task.updated']);
});

it('records nothing when the update changes no field', function () {
    $this->putJson("/api/tasks/{$this->task->id}", taskUpdatePayload([
        'name'        => 'Original',
        'description' => 'Original body.',
        'status'      => TaskStatus::Open->value,
        'priority'    => TaskPriority::High->value,
        'start_date'  => '2026-01-01',
        'due_date'    => '2026-01-31',
    ]))->assertOk();

    expect(AuditRecordModel::query()->count())->toBe(0);
});
