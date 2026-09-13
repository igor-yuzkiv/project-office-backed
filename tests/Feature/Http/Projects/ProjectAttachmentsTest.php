<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('attachments');

    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();
});

it('uploads a file through the project route', function () {
    $response = $this->post("/api/projects/{$this->project->id}/attachments", [
        'file' => UploadedFile::fake()->createWithContent('notes.md', '# Notes'),
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.original_name', 'notes.md');

    expect($this->project->attachments()->count())->toBe(1);
    Storage::disk('attachments')->assertExists($this->project->attachments()->first()->storage_key);
});

it('reads attachments through the project route', function () {
    $this->post("/api/projects/{$this->project->id}/attachments", [
        'file' => UploadedFile::fake()->createWithContent('spec.pdf', 'spec'),
    ])->assertCreated();

    $response = $this->getJson("/api/projects/{$this->project->id}/attachments");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.original_name'))->toBe('spec.pdf');
});

it('does not return attachments of another project', function () {
    $other = ProjectModel::factory()->create();
    $this->post("/api/projects/{$other->id}/attachments", [
        'file' => UploadedFile::fake()->createWithContent('other.txt', 'other'),
    ])->assertCreated();

    $response = $this->getJson("/api/projects/{$this->project->id}/attachments");

    $response->assertOk();
    expect($response->json('data'))->toBeEmpty();
});

it('requires authentication', function () {
    auth()->forgetGuards();

    $this->getJson("/api/projects/{$this->project->id}/attachments")->assertUnauthorized();
});
