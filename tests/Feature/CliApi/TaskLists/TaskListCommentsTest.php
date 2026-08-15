<?php

use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create(['prefix' => 'MTM']);
    $this->taskList = TaskListModel::factory()->create([
        'project_id'      => $this->project->id,
        'key'             => 'MTM-TL-7',
        'sequence_number' => 7,
    ]);
});

it('creates all comments from the array in a single request', function () {
    $response = $this->postJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/comments", [
        'comments' => [
            ['content' => 'First comment'],
            ['content' => 'Second comment'],
        ],
    ]);

    $response->assertCreated();

    expect($response->json('data'))->toHaveCount(2)
        ->and($response->json('data.0.content'))->toBe('First comment')
        ->and(CommentModel::query()->count())->toBe(2)
        ->and($this->taskList->comments()->count())->toBe(2);
});

it('rejects a request without a comments field', function () {
    $this->postJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/comments", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['comments']);
});

it('returns the comments of the task list, newest first and paginated', function () {
    $author = UserModel::factory()->create();

    foreach (range(1, 3) as $i) {
        $this->travelTo(now()->addMinutes($i));

        $this->taskList->comments()->create([
            'author_id' => $author->id,
            'content'   => "Comment {$i}",
        ]);
    }

    $this->travelBack();

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/comments?per_page=2");

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links'])
        ->assertJsonPath('data.0.content', 'Comment 3');

    expect($response->json('meta.total'))->toBe(3)
        ->and($response->json('data'))->toHaveCount(2);
});

it('does not return comments of another task list', function () {
    $otherList = TaskListModel::factory()->create([
        'project_id'      => $this->project->id,
        'key'             => 'MTM-TL-8',
        'sequence_number' => 8,
    ]);
    $otherList->comments()->create([
        'author_id' => UserModel::factory()->create()->id,
        'content'   => 'Other list comment',
    ]);

    $response = $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/comments");

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(0);
});

it('does not reach a task list of another project', function () {
    $otherProject = ProjectModel::factory()->create(['prefix' => 'OTH']);
    TaskListModel::factory()->create([
        'project_id'      => $otherProject->id,
        'key'             => 'OTH-TL-1',
        'sequence_number' => 1,
    ]);

    $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/OTH-TL-1/comments")
        ->assertNotFound();

    $this->postJson("/api/cli/projects/{$this->project->id}/task-lists/OTH-TL-1/comments", [
        'comments' => [['content' => 'Hijacked']],
    ])->assertNotFound();
});

it('rejects an unauthenticated request', function () {
    auth()->forgetGuards();

    $this->getJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/comments")
        ->assertUnauthorized();
});

it('records one audit event per comment in a batch request', function () {
    $this->postJson("/api/cli/projects/{$this->project->id}/task-lists/MTM-TL-7/comments", [
        'comments' => [
            ['content' => 'First comment'],
            ['content' => 'Second comment'],
        ],
    ])->assertCreated();

    $records = AuditRecordModel::query()->orderBy('id')->get();

    expect($records)->toHaveCount(2)
        ->and($records->pluck('type')->all())->toBe(['comment.created', 'comment.created'])
        ->and($records->pluck('description')->all())->toBe(['First comment', 'Second comment'])
        ->and($records->first()->title)->toBe("{$this->user->name} commented on «{$this->taskList->name}»")
        ->and($records->pluck('subject_id')->unique()->all())->toBe([$this->taskList->id]);
});
