<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** The numbering pattern the collation exists for, in the order a person reads it. */
$numbered = [
    '1. Data model',
    '2. Backend API',
    '3. Pay Rate form',
    '9. Validation',
    '10. Documentation',
    '11. Cleanup',
];

it('orders task names with numeric prefixes as numbers', function () use ($numbered) {
    $project = ProjectModel::factory()->create();

    foreach (array_reverse($numbered) as $name) {
        TaskModel::factory()->create(['project_id' => $project->id, 'name' => $name]);
    }

    expect(TaskModel::orderBy('name')->pluck('name')->all())->toBe($numbered);
});

it('orders task list names with numeric prefixes as numbers', function () use ($numbered) {
    $project = ProjectModel::factory()->create();

    foreach (array_reverse($numbered) as $name) {
        TaskListModel::factory()->create(['project_id' => $project->id, 'name' => $name]);
    }

    expect(TaskListModel::orderBy('name')->pluck('name')->all())->toBe($numbered);
});

it('orders project names with numeric prefixes as numbers', function () use ($numbered) {
    foreach (array_reverse($numbered) as $name) {
        ProjectModel::factory()->create(['name' => $name]);
    }

    expect(ProjectModel::orderBy('name')->pluck('name')->all())->toBe($numbered);
});

it('reverses the same order when sorting descending', function () use ($numbered) {
    $project = ProjectModel::factory()->create();

    foreach ($numbered as $name) {
        TaskModel::factory()->create(['project_id' => $project->id, 'name' => $name]);
    }

    expect(TaskModel::orderByDesc('name')->pluck('name')->all())->toBe(array_reverse($numbered));
});

it('sorts names without a numeric prefix after the numbered ones', function () {
    $project = ProjectModel::factory()->create();

    foreach (['Ad-hoc task', '2. Backend API', '10. Documentation'] as $name) {
        TaskModel::factory()->create(['project_id' => $project->id, 'name' => $name]);
    }

    expect(TaskModel::orderBy('name')->pluck('name')->all())
        ->toBe(['2. Backend API', '10. Documentation', 'Ad-hoc task']);
});

it('falls back to the text when the numbers are equal', function () {
    $project = ProjectModel::factory()->create();

    foreach (['1. Beta', '', '1. Alpha'] as $name) {
        TaskModel::factory()->create(['project_id' => $project->id, 'name' => $name]);
    }

    expect(TaskModel::orderBy('name')->pluck('name')->all())->toBe(['', '1. Alpha', '1. Beta']);
});

it('still matches an exact name and a partial name, and still misses what does not match', function () {
    $project = ProjectModel::factory()->create();
    TaskModel::factory()->create(['project_id' => $project->id, 'name' => '10. Documentation']);

    expect(TaskModel::where('name', '10. Documentation')->exists())->toBeTrue()
        ->and(TaskModel::where('name', 'like', '%Documentation%')->exists())->toBeTrue()
        ->and(TaskModel::where('name', '10 Documentation')->exists())->toBeFalse()
        ->and(TaskModel::where('name', 'like', '%Validation%')->exists())->toBeFalse();
});
