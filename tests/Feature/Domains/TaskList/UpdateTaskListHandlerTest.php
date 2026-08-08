<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\TaskList\Actions\UpdateTaskList\UpdateTaskListCommand;
use App\Domains\TaskList\Actions\UpdateTaskList\UpdateTaskListHandler;
use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->taskList = TaskListModel::factory()->create([
        'project_id'  => ProjectModel::factory()->create()->id,
        'name'        => 'Backlog',
        'status'      => TaskListStatus::Open->value,
        'description' => 'Original',
    ]);
    $this->handler = app(UpdateTaskListHandler::class);
});

it('updates name, status and description', function () {
    $updated = $this->handler->handle(new UpdateTaskListCommand(
        taskList: $this->taskList,
        name: 'Sprint 4',
        status: TaskListStatus::Completed,
        description: '# Rewritten',
        descriptionProvided: true,
    ));

    expect($updated->name)->toBe('Sprint 4')
        ->and($updated->status)->toBe(TaskListStatus::Completed)
        ->and($updated->description)->toBe('# Rewritten');
});

it('leaves fields that were not provided untouched', function () {
    $updated = $this->handler->handle(new UpdateTaskListCommand(
        taskList: $this->taskList,
        status: TaskListStatus::InProgress,
    ));

    expect($updated->status)->toBe(TaskListStatus::InProgress)
        ->and($updated->name)->toBe('Backlog')
        ->and($updated->description)->toBe('Original');
});

it('clears the description when one is explicitly provided as null', function () {
    $updated = $this->handler->handle(new UpdateTaskListCommand(
        taskList: $this->taskList,
        description: null,
        descriptionProvided: true,
    ));

    expect($updated->description)->toBeNull();
});

it('never rewrites the key', function () {
    $key = $this->taskList->key;

    $updated = $this->handler->handle(new UpdateTaskListCommand(
        taskList: $this->taskList,
        name: 'Renamed',
    ));

    expect($updated->key)->toBe($key);
});

it('replaces the tag set only when tag ids are given', function () {
    $first = TagModel::factory()->create();
    $second = TagModel::factory()->create();
    $this->taskList->tags()->sync([$first->id]);

    $this->handler->handle(new UpdateTaskListCommand(taskList: $this->taskList, name: 'Renamed'));
    expect($this->taskList->fresh()->tags->pluck('id')->all())->toBe([$first->id]);

    $this->handler->handle(new UpdateTaskListCommand(taskList: $this->taskList, tagIds: [$second->id]));
    expect($this->taskList->fresh()->tags->pluck('id')->all())->toBe([$second->id]);
});
