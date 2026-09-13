<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
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
            'data' => [['id', 'type', 'title', 'description', 'created_at', 'subject', 'project', 'actor']],
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
    expect($record)->toHaveKeys(['actor', 'subject', 'project'])
        ->and($record['actor'])->toBeNull()
        ->and($record['subject'])->toBeNull()
        ->and($record['project'])->toBeNull();
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

function feedFilter(string $field, mixed $value, ?string $matchMode = null, string $filterKey = 'text'): array
{
    return ['filters' => [array_filter([
        'filter_key' => $filterKey,
        'field_name' => $field,
        'value'      => $value,
        'matchMode'  => $matchMode,
    ], fn ($v) => $v !== null)]];
}

function feedUrl(array $query): string
{
    return '/api/audit-records?'.http_build_query($query);
}

it('returns the subject key and name with the project', function () {
    $task = taskForFeed();
    AuditRecordModel::factory()->forSubject($task)->create();

    $this->getJson('/api/audit-records')
        ->assertOk()
        ->assertJsonPath('data.0.subject', ['type' => 'task', 'id' => $task->id, 'key' => $task->key, 'name' => $task->name])
        ->assertJsonPath('data.0.project', ['id' => $task->project->id, 'name' => $task->project->name, 'prefix' => $task->project->prefix]);
});

it('names a document by its title and a project by its prefix', function () {
    $project = ProjectModel::factory()->create();
    $document = ProjectDocumentModel::factory()->create(['project_id' => $project->id]);
    AuditRecordModel::factory()->forSubject($document)->create();
    AuditRecordModel::factory()->forSubject($project)->create();

    $this->getJson('/api/audit-records')
        ->assertOk()
        ->assertJsonPath('data.0.subject.key', $project->prefix)
        ->assertJsonPath('data.0.subject.name', $project->name)
        ->assertJsonPath('data.1.subject.name', $document->title);
});

it('keeps type and id of a deleted subject and drops its key and name', function () {
    $task = taskForFeed();
    AuditRecordModel::factory()->forSubject($task)->create();
    $task->forceDelete();

    $this->getJson('/api/audit-records')
        ->assertOk()
        ->assertJsonPath('data.0.subject', ['type' => 'task', 'id' => $task->id, 'key' => null, 'name' => null]);
});

it('filters by project_id', function () {
    $mine = taskForFeed();
    AuditRecordModel::factory()->forSubject($mine)->create();
    AuditRecordModel::factory()->forSubject(taskForFeed())->create();

    $this->getJson(feedUrl(feedFilter('project_id', $mine->project_id, filterKey: 'lookup')))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.project.id', $mine->project_id);
});

it('filters by type', function () {
    AuditRecordModel::factory()->create(['type' => 'task.created']);
    AuditRecordModel::factory()->create(['type' => 'task.updated']);
    AuditRecordModel::factory()->create(['type' => 'task_list.created']);

    $this->getJson(feedUrl(feedFilter('type', ['task.created', 'task_list.created'], 'in')))
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->getJson(feedUrl(feedFilter('type', 'task.updated', 'equals')))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'task.updated');
});

it('filters by subject_type using the short key', function () {
    $task = taskForFeed();
    $list = TaskListModel::factory()->create(['project_id' => $task->project_id]);
    AuditRecordModel::factory()->forSubject($task)->create();
    AuditRecordModel::factory()->forSubject($list)->create();
    AuditRecordModel::factory()->forSubject($task->project)->create();

    $this->getJson(feedUrl(feedFilter('subject_type', 'task_list', 'equals')))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.subject.type', 'task_list');

    $this->getJson(feedUrl(feedFilter('subject_type', ['task', 'project'], 'in')))
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('combines filters', function () {
    $task = taskForFeed();
    AuditRecordModel::factory()->forSubject($task)->create(['type' => 'task.created']);
    AuditRecordModel::factory()->forSubject($task)->create(['type' => 'task.updated']);
    AuditRecordModel::factory()->forSubject(taskForFeed())->create(['type' => 'task.created']);

    $this->getJson(feedUrl(['filters' => [
        ['filter_key' => 'lookup', 'field_name' => 'project_id', 'value' => $task->project_id],
        ['filter_key' => 'text', 'field_name' => 'type', 'value' => 'task.created', 'matchMode' => 'equals'],
        ['filter_key' => 'text', 'field_name' => 'subject_type', 'value' => ['task'], 'matchMode' => 'in'],
    ]]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'task.created')
        ->assertJsonPath('data.0.project.id', $task->project_id);
});

it('rejects a filter on a field the feed does not expose', function () {
    $this->getJson(feedUrl(feedFilter('title', 'x')))->assertStatus(400);
});
