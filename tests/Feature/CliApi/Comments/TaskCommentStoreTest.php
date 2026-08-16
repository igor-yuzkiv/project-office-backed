<?php

use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
    $this->task = TaskModel::factory()->create(['project_id' => $this->project->id]);
});

it('creates all comments from the array in a single request', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
        'comments' => [
            ['content' => 'First comment'],
            ['content' => 'Second comment'],
        ],
    ]);

    $response->assertCreated();

    expect($response->json('data'))->toHaveCount(2)
        ->and(CommentModel::query()->count())->toBe(2);

    expect($response->json('data.0.content'))->toBe('First comment')
        ->and($response->json('data.1.content'))->toBe('Second comment');
});

it('rejects an empty comments array', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
        'comments' => [],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['comments']);
});

it('rejects a comment missing content', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
        'comments' => [
            ['content' => 'Valid comment'],
            ['content' => ''],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['comments.1.content']);
});

it('rejects a request without a comments field', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['comments']);
});

it('records one audit event per comment in a batch request', function () {
    $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
        'comments' => [
            ['content' => 'First comment'],
            ['content' => 'Second comment'],
        ],
    ])->assertCreated();

    $records = AuditRecordModel::query()->orderBy('id')->get();

    expect($records)->toHaveCount(2)
        ->and($records->pluck('type')->all())->toBe(['comment.created', 'comment.created'])
        ->and($records->pluck('description')->all())->toBe(['First comment', 'Second comment'])
        ->and($records->pluck('subject_id')->unique()->all())->toBe([$this->task->id]);
});
