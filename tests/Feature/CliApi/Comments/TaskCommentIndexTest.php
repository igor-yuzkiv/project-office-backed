<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
    $this->task = TaskModel::factory()->create(['project_id' => $this->project->id]);
});

it('returns paginated comments for the task', function () {
    $this->task->comments()->createMany(
        collect(range(1, 3))->map(fn (int $i) => [
            'author_id' => UserModel::factory()->create()->id,
            'content'   => "Comment {$i}",
        ])->all()
    );

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments");

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);

    expect($response->json('meta.total'))->toBe(3);
});

it('paginates comments using page and per_page query params', function () {
    $this->task->comments()->createMany(
        collect(range(1, 5))->map(fn (int $i) => [
            'author_id' => UserModel::factory()->create()->id,
            'content'   => "Comment {$i}",
        ])->all()
    );

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments?per_page=2&page=2");

    $response->assertOk();

    expect($response->json('meta.per_page'))->toBe(2)
        ->and($response->json('meta.current_page'))->toBe(2)
        ->and($response->json('meta.total'))->toBe(5)
        ->and($response->json('data'))->toHaveCount(2);
});

it('does not return comments from other tasks', function () {
    $otherTask = TaskModel::factory()->create(['project_id' => $this->project->id]);
    $otherTask->comments()->create([
        'author_id' => UserModel::factory()->create()->id,
        'content'   => 'Other task comment',
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments");

    $response->assertOk();

    expect($response->json('meta.total'))->toBe(0);
});
