<?php

use App\Domains\Comment\Actions\CreateComment\CreateCommentHandler;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
});

it('creates a handoff resolution comment and sets the status to ready_to_test', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::InProgress->value]);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/handoff", [
        'resolution' => 'Implemented and covered with tests.',
    ]);

    $response->assertOk();
    expect($response->json('data.status'))->toBe(TaskStatus::ReadyToTest->value);

    $comment = CommentModel::query()->where('commentable_id', $task->id)->sole();
    expect($comment->content)->toContain('# Handoff')->toContain('Implemented and covered with tests.');

    expect($task->fresh()->status)->toBe(TaskStatus::ReadyToTest);
});

it('requires a resolution', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id]);

    $response = $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/handoff", []);

    $response->assertUnprocessable()->assertJsonValidationErrors(['resolution']);
});

it('does not change the status if comment creation fails', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::InProgress->value]);

    $this->mock(CreateCommentHandler::class, function ($mock) {
        $mock->shouldReceive('handle')->once()->andThrow(new RuntimeException('comment creation failed'));
    });

    $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/handoff", [
        'resolution' => 'Should not be applied.',
    ])->assertServerError();

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
    expect(CommentModel::query()->where('commentable_id', $task->id)->count())->toBe(0);
});

it('records exactly one task.handoff event and nothing else', function () {
    $task = TaskModel::factory()->create(['project_id' => $this->project->id, 'status' => TaskStatus::InProgress->value]);

    $this->postJson("/api/cli/projects/{$this->project->id}/tasks/{$task->id}/workflow/handoff", [
        'resolution' => 'Implemented and covered with tests.',
    ])->assertOk();

    // The full list of types, not three separate absence checks: this catches a stray
    // comment.created and a stray task.status_changed in one assertion.
    expect(AuditRecordModel::query()->orderBy('id')->pluck('type')->all())->toBe(['task.handoff']);

    $record = AuditRecordModel::query()->sole();

    expect($record->title)->toBe("{$this->user->name} handed off {$task->key} for testing")
        ->and($record->description)->toBe('Implemented and covered with tests.');
});
