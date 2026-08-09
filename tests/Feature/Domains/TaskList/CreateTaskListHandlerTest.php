<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\TaskList\Actions\CreateTaskList\CreateTaskListCommand;
use App\Domains\TaskList\Actions\CreateTaskList\CreateTaskListHandler;
use App\Domains\TaskList\Enums\TaskListStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->project = ProjectModel::factory()->create(['prefix' => 'MTM']);
    $this->handler = app(CreateTaskListHandler::class);
});

it('creates a task list with a resolved key and the default status', function () {
    $taskList = $this->handler->handle(new CreateTaskListCommand(
        projectId: $this->project->id,
        name: 'Backlog',
    ));

    expect($taskList->key)->toBe('MTM-TL-1')
        ->and($taskList->sequence_number)->toBe(1)
        ->and($taskList->name)->toBe('Backlog')
        ->and($taskList->status)->toBe(TaskListStatus::Open)
        ->and($taskList->description)->toBeNull();
});

it('persists the given status, description and tags', function () {
    $tag = TagModel::factory()->create();

    $taskList = $this->handler->handle(new CreateTaskListCommand(
        projectId: $this->project->id,
        name: 'Sprint 4',
        status: TaskListStatus::InProgress,
        description: '# Notes',
        tagIds: [$tag->id],
    ));

    $taskList->refresh();

    expect($taskList->status)->toBe(TaskListStatus::InProgress)
        ->and($taskList->description)->toBe('# Notes')
        ->and($taskList->tags->pluck('id')->all())->toBe([$tag->id]);
});

it('numbers task lists sequentially within a project', function () {
    $this->handler->handle(new CreateTaskListCommand(projectId: $this->project->id, name: 'First'));
    $second = $this->handler->handle(new CreateTaskListCommand(projectId: $this->project->id, name: 'Second'));

    expect($second->key)->toBe('MTM-TL-2');
});

it('fails when the project does not exist', function () {
    $this->handler->handle(new CreateTaskListCommand(
        projectId: '01jz0000000000000000000000',
        name: 'Orphan',
    ));
})->throws(ModelNotFoundException::class);
