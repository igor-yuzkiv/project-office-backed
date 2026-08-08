<?php

use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->taskList = TaskListModel::factory()->create([
        'project_id' => ProjectModel::factory()->create()->id,
    ]);
});

it('attaches tags through the shared taggables table', function () {
    $tag = TagModel::factory()->create();

    $this->taskList->tags()->sync([$tag->id]);

    expect($this->taskList->fresh()->tags->pluck('id')->all())->toBe([$tag->id]);
});

it('stores comments through the shared commentable columns', function () {
    $comment = CommentModel::factory()->make(['author_id' => UserModel::factory()->create()->id]);

    $this->taskList->comments()->save($comment);

    expect($this->taskList->fresh()->comments)->toHaveCount(1)
        ->and($comment->fresh()->commentable_id)->toBe($this->taskList->id);
});

it('stores attachments through the shared attachable columns', function () {
    $attachment = $this->taskList->attachments()->create([
        'original_name'    => 'notes.md',
        'extension'        => 'md',
        'mime_type'        => 'text/markdown',
        'size_bytes'       => 128,
        'storage_provider' => 's3',
        'storage_key'      => 'task-lists/notes.md',
    ]);

    expect($this->taskList->fresh()->attachments->pluck('id')->all())->toBe([$attachment->id]);
});
