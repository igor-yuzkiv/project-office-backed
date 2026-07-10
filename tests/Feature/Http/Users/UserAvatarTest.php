<?php

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('attachments');
    $this->user = UserModel::factory()->create();
});

it('uploads and attaches a new avatar', function () {
    $this->actingAs($this->user);
    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->postJson('/api/user/avatar', ['avatar' => $file]);

    $response->assertOk()
        ->assertJsonStructure(['data' => ['id', 'name', 'email', 'avatar_url']]);

    expect($response->json('data.avatar_url'))->not->toBeNull();

    $this->user->refresh();

    expect($this->user->current_avatar_attachment_id)->not->toBeNull();

    $attachment = AttachmentModel::find($this->user->current_avatar_attachment_id);
    expect($attachment)->not->toBeNull()
        ->and($attachment->role)->toBe('avatar')
        ->and($attachment->attachable_type)->toBe(UserModel::class)
        ->and($attachment->attachable_id)->toBe($this->user->id);

    Storage::disk('attachments')->assertExists($attachment->storage_key);
});

it('deletes the previous avatar when a new one is uploaded', function () {
    $this->actingAs($this->user);

    $firstFile = UploadedFile::fake()->image('first.jpg');
    $this->postJson('/api/user/avatar', ['avatar' => $firstFile])->assertOk();

    $this->user->refresh();
    $previousAttachment = AttachmentModel::find($this->user->current_avatar_attachment_id);
    $previousStorageKey = $previousAttachment->storage_key;

    $secondFile = UploadedFile::fake()->image('second.jpg');
    $response = $this->postJson('/api/user/avatar', ['avatar' => $secondFile]);

    $response->assertOk();
    $this->user->refresh();

    expect($this->user->current_avatar_attachment_id)->not->toBe($previousAttachment->id);
    expect(AttachmentModel::find($previousAttachment->id))->toBeNull();

    Storage::disk('attachments')->assertMissing($previousStorageKey);
});

it('rejects a non-image file', function () {
    $this->actingAs($this->user);

    $file = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

    $response = $this->postJson('/api/user/avatar', ['avatar' => $file]);

    $response->assertStatus(422)->assertJsonValidationErrors('avatar');
});

it('rejects a file exceeding the size limit', function () {
    $this->actingAs($this->user);

    $file = UploadedFile::fake()->create('avatar.jpg', 5121, 'image/jpeg');

    $response = $this->postJson('/api/user/avatar', ['avatar' => $file]);

    $response->assertStatus(422)->assertJsonValidationErrors('avatar');
});

it('requires authentication', function () {
    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->postJson('/api/user/avatar', ['avatar' => $file]);

    $response->assertUnauthorized();
});
