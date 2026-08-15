<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Http\Shared\Resources\AuditTrail\AuditRecordResource;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->actingAs($this->user);
});

function countFeedQueries(Closure $callback): int
{
    $count = 0;
    DB::listen(function () use (&$count): void {
        $count++;
    });

    $callback();

    return $count;
}

function taskForFeed(): TaskModel
{
    return TaskModel::factory()->create(['project_id' => ProjectModel::factory()->create()->id]);
}

it('returns a paginated envelope', function () {
    AuditRecordModel::factory()->count(3)->create();

    $this->getJson('/api/audit-records')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'type', 'title', 'description', 'created_at', 'subject', 'actor']],
            'meta',
            'links',
        ]);
});

it('orders records from newest to oldest', function () {
    $records = collect(range(1, 3))->map(fn () => AuditRecordModel::factory()->create());

    $this->getJson('/api/audit-records')
        ->assertOk()
        ->assertJsonPath('data.0.id', $records->last()->id)
        ->assertJsonPath('data.2.id', $records->first()->id);
});

it('respects per_page and page', function () {
    AuditRecordModel::factory()->count(3)->create();

    $this->getJson('/api/audit-records?per_page=2&page=2')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.total', 3);
});

it('ignores sort_by and sort_order', function () {
    // Titles descend as records are inserted, so honouring sort_by=title&sort_order=asc would
    // deterministically put the oldest record first instead of the newest.
    $records = collect(['C', 'B', 'A'])->map(fn (string $title) => AuditRecordModel::factory()->create(['title' => $title]));

    $this->getJson('/api/audit-records?sort_by=title&sort_order=asc')
        ->assertOk()
        ->assertJsonPath('data.0.id', $records->last()->id)
        ->assertJsonPath('data.0.title', 'A');
});

it('keeps actor and subject present as null rather than dropping the keys', function () {
    AuditRecordModel::factory()->create(['created_by' => null]);

    $record = $this->getJson('/api/audit-records')->assertOk()->json('data.0');

    // assertJsonPath(..., null) cannot tell a null value from a missing key, and a missing key is
    // a different contract for whoever reads this.
    expect($record)->toHaveKeys(['actor', 'subject'])
        ->and($record['actor'])->toBeNull()
        ->and($record['subject'])->toBeNull();
});

it('keeps actor present even when the relation was never loaded', function () {
    AuditRecordModel::factory()->create(['created_by' => null]);

    // Straight at the resource, with createdBy deliberately not eager-loaded: through the endpoint
    // the controller always loads it, so whenLoaded() would look correct there and drop the key
    // everywhere else.
    $payload = (new AuditRecordResource(AuditRecordModel::query()->sole()))->toArray(request());

    expect($payload)->toHaveKeys(['actor', 'subject'])
        ->and($payload['actor'])->toBeNull();
});

it('returns the author of a record that has one', function () {
    AuditRecordModel::factory()->create(['created_by' => $this->user->id]);

    $this->getJson('/api/audit-records')
        ->assertOk()
        ->assertJsonPath('data.0.actor.id', $this->user->id)
        ->assertJsonPath('data.0.actor.name', $this->user->name);
});

it('returns the subject type as a short key without a namespace', function () {
    $task = taskForFeed();
    AuditRecordModel::factory()->forSubject($task)->create();

    $this->getJson('/api/audit-records')
        ->assertOk()
        ->assertJsonPath('data.0.subject.type', 'task')
        ->assertJsonPath('data.0.subject.id', $task->id);
});

it('derives a multi-word subject type in snake_case', function () {
    $document = ProjectDocumentModel::factory()->create([
        'project_id' => ProjectModel::factory()->create()->id,
    ]);
    AuditRecordModel::factory()->forSubject($document)->create();

    $this->getJson('/api/audit-records')
        ->assertOk()
        ->assertJsonPath('data.0.subject.type', 'project_document');
});

it('serializes created_at the same way the task resource does', function () {
    $task = taskForFeed();
    $record = AuditRecordModel::factory()->create(['created_at' => $task->created_at]);

    $feed = $this->getJson('/api/audit-records')->assertOk();
    $tasks = $this->getJson('/api/tasks')->assertOk();

    expect($feed->json('data.0.created_at'))->toBe($tasks->json('data.0.created_at'))
        ->and($feed->json('data.0.id'))->toBe($record->id);
});

it('rejects an unauthenticated request', function () {
    auth()->logout();

    $this->getJson('/api/audit-records')->assertUnauthorized();
});

it('keeps the query count flat as the page grows', function () {
    AuditRecordModel::factory()->count(20)->create();

    $oneRow = countFeedQueries(fn () => $this->getJson('/api/audit-records?per_page=1')->assertOk());
    $twentyRows = countFeedQueries(fn () => $this->getJson('/api/audit-records?per_page=20')->assertOk());

    // Page size is the axis that matters: without the eager load each row fetches its own author,
    // so twenty rows cost nineteen queries more than one. Author count is not the axis — Eloquent
    // has no identity map, so twenty rows sharing one author would still be twenty queries.
    expect($twentyRows)->toBe($oneRow)
        ->and($twentyRows)->toBeLessThanOrEqual(5);
});
