<?php

use App\Domains\Tag\Models\TagModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
});

it('normalizes name by trimming and lowercasing', function () {
    $response = $this->postJson('/api/tags', [
        'name'  => '  My Tag  ',
        'color' => '#FF0000',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'my tag');

    $this->assertDatabaseHas('tags', ['name' => 'my tag']);
});

it('generates a random color when color is not provided', function () {
    $response = $this->postJson('/api/tags', [
        'name' => 'no-color-tag',
    ]);

    $response->assertCreated();

    $color = $response->json('data.color');
    expect($color)->toMatch('/^#[0-9A-Fa-f]{6}$/');
});

it('returns 422 when tag name is a duplicate', function () {
    TagModel::create(['name' => 'duplicate', 'color' => '#AABBCC']);

    $response = $this->postJson('/api/tags', [
        'name'  => 'duplicate',
        'color' => '#112233',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name'])
        ->assertJsonPath('errors.name.0', 'Tag with this name already exists.');
});

it('normalizes input before duplicate check', function () {
    TagModel::create(['name' => 'existing', 'color' => '#AABBCC']);

    $response = $this->postJson('/api/tags', [
        'name'  => '  EXISTING  ',
        'color' => '#112233',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('returns tags matching the search substring', function () {
    TagModel::create(['name' => 'backend', 'color' => '#111111']);
    TagModel::create(['name' => 'frontend', 'color' => '#222222']);
    TagModel::create(['name' => 'devops', 'color' => '#333333']);

    $response = $this->getJson('/api/tags?search=end');

    $response->assertOk();

    $names = collect($response->json('data'))->pluck('name')->sort()->values()->all();
    expect($names)->toBe(['backend', 'frontend']);
});

it('returns all tags when search is not provided', function () {
    TagModel::create(['name' => 'alpha', 'color' => '#AAAAAA']);
    TagModel::create(['name' => 'beta', 'color' => '#BBBBBB']);

    $response = $this->getJson('/api/tags');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

it('returns tags ordered by name ascending', function () {
    TagModel::create(['name' => 'zebra', 'color' => '#AAAAAA']);
    TagModel::create(['name' => 'apple', 'color' => '#BBBBBB']);
    TagModel::create(['name' => 'mango', 'color' => '#CCCCCC']);

    $response = $this->getJson('/api/tags');

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name')->all();
    expect($names)->toBe(['apple', 'mango', 'zebra']);
});
