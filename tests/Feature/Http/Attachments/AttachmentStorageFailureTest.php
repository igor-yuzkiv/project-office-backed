<?php

use App\Domains\Attachment\Services\AttachmentStorageService;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

uses(RefreshDatabase::class);

/** A storage backend that accepts nothing, which is what a full disk or a dead bucket looks like. */
class RefusingAttachmentStorage implements AttachmentStorageService
{
    public function getProvider(): string
    {
        return 'refusing';
    }

    public function getDiskName(): string
    {
        return 'attachments';
    }

    public function store(UploadedFile $file, string $path): bool
    {
        return false;
    }

    public function exists(string $path): bool
    {
        return false;
    }

    public function delete(string $path): void {}

    public function temporaryUrl(string $path): string
    {
        return '';
    }

    public function streamResponse(string $path, ?string $originName = null): StreamedResponse
    {
        return new StreamedResponse;
    }
}

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
    $this->project = ProjectModel::factory()->create();

    $this->app->instance(AttachmentStorageService::class, new RefusingAttachmentStorage);
});

// The request was well formed and there is nothing for the caller to correct, so this is not a
// 422 like the domain's other refusals.
it('answers 500 with a readable message when the file cannot be stored', function () {
    $response = $this->postJson("/api/projects/{$this->project->id}/attachments", [
        'file' => UploadedFile::fake()->createWithContent('notes.md', '# Notes'),
    ]);

    $response->assertStatus(500)
        ->assertJsonPath('message', 'Attachment file could not be stored.');
});

it('records no attachment when the file could not be stored', function () {
    $this->postJson("/api/projects/{$this->project->id}/attachments", [
        'file' => UploadedFile::fake()->createWithContent('notes.md', '# Notes'),
    ])->assertStatus(500);

    expect($this->project->attachments()->count())->toBe(0);
});
