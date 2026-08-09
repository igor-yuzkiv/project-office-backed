<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('attachments');

    $this->actingAs(UserModel::factory()->create());
    $this->taskList = TaskListModel::factory()->create([
        'project_id' => ProjectModel::factory()->create()->id,
    ]);
});

it('uploads a file through the task list route', function () {
    $response = $this->post("/api/task-lists/{$this->taskList->id}/attachments", [
        'file' => UploadedFile::fake()->createWithContent('notes.md', '# Notes'),
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.original_name', 'notes.md');

    expect($this->taskList->attachments()->count())->toBe(1);
    Storage::disk('attachments')->assertExists($this->taskList->attachments()->first()->storage_key);
});

it('reads attachments through the task list route', function () {
    $this->post("/api/task-lists/{$this->taskList->id}/attachments", [
        'file' => UploadedFile::fake()->createWithContent('spec.pdf', 'spec'),
    ])->assertCreated();

    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/attachments");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.original_name'))->toBe('spec.pdf');
});

it('does not return attachments of another task list', function () {
    $otherList = TaskListModel::factory()->create(['project_id' => $this->taskList->project_id]);
    $this->post("/api/task-lists/{$otherList->id}/attachments", [
        'file' => UploadedFile::fake()->createWithContent('other.txt', 'other'),
    ])->assertCreated();

    $response = $this->getJson("/api/task-lists/{$this->taskList->id}/attachments");

    $response->assertOk();
    expect($response->json('data'))->toBeEmpty();
});

it('requires a file', function () {
    $this->postJson("/api/task-lists/{$this->taskList->id}/attachments", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('file');
});

it('requires authentication', function () {
    auth()->forgetGuards();

    $this->getJson("/api/task-lists/{$this->taskList->id}/attachments")->assertUnauthorized();
});

// Download and deletion stay on the universal attachment routes — no consumer-scoped
// endpoints are added for them.
it('downloads and deletes a task list attachment through the universal routes', function () {
    $attachmentId = $this->post("/api/task-lists/{$this->taskList->id}/attachments", [
        'file' => UploadedFile::fake()->createWithContent('report.txt', 'report body'),
    ])->assertCreated()->json('data.id');

    $this->get("/api/attachments/{$attachmentId}/download")->assertOk();

    $this->deleteJson("/api/attachments/{$attachmentId}")->assertOk();

    expect($this->taskList->attachments()->count())->toBe(0);
});
