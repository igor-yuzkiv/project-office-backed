<?php

use App\Domains\Comment\Enums\CommentKind;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Actions\CliAgentWorkflow\CheckpointTask\CheckpointComment;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->task = TaskModel::factory()->create([
        'project_id' => ProjectModel::factory()->create()->id,
    ]);
});

it('creates a comment on the task', function () {
    $this->postJson("/api/tasks/{$this->task->id}/comments", ['content' => 'Looks good.'])
        ->assertCreated();

    expect(CommentModel::query()->sole()->content)->toBe('Looks good.');
});

it('records exactly one comment.created event pointing at the task', function () {
    $this->postJson("/api/tasks/{$this->task->id}/comments", ['content' => 'Looks good.'])
        ->assertCreated();

    $record = AuditRecordModel::query()->sole();

    expect($record->type)->toBe('comment.created')
        ->and($record->title)->toBe("{$this->user->name} commented on {$this->task->key}")
        ->and($record->description)->toBe('Looks good.')
        ->and($record->subject_type)->toBe(TaskModel::class)
        ->and($record->subject_id)->toBe($this->task->id);
});

it('marks a plain comment as kind comment and a checkpoint as kind checkpoint', function () {
    $this->task->comments()->createMany([
        ['author_id' => $this->user->id, 'content' => 'Looks good.'],
        ['author_id' => $this->user->id, 'content' => CheckpointComment::PREFIX."Investigated\n\nRoot cause found.", 'kind' => CommentKind::Checkpoint],
        ['author_id' => $this->user->id, 'content' => CheckpointComment::HANDOFF_PREFIX."\n\nDone: shipped.", 'kind' => CommentKind::Handoff],
    ]);

    $kinds = collect($this->getJson("/api/tasks/{$this->task->id}/comments")->assertOk()->json('data'))
        ->pluck('kind', 'content');

    expect($kinds['Looks good.'])->toBe('comment')
        ->and($kinds[CheckpointComment::PREFIX."Investigated\n\nRoot cause found."])->toBe('checkpoint')
        ->and($kinds[CheckpointComment::HANDOFF_PREFIX."\n\nDone: shipped."])->toBe('handoff');
});

it('stores a comment posted by a person as kind comment whatever its text', function () {
    $this->postJson("/api/tasks/{$this->task->id}/comments", ['content' => '# Checkpoint: not really'])
        ->assertCreated()
        ->assertJsonPath('data.kind', 'comment');
});
