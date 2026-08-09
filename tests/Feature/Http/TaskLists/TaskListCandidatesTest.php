<?php

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(UserModel::factory()->create());
    $this->project = ProjectModel::factory()->create();
    $this->taskList = TaskListModel::factory()->create(['project_id' => $this->project->id]);

    $this->unassigned = TaskModel::factory()->count(2)->create([
        'project_id'   => $this->project->id,
        'task_list_id' => null,
    ]);
    TaskModel::factory()->create([
        'project_id'   => $this->project->id,
        'task_list_id' => $this->taskList->id,
    ]);
});

function searchCandidates(string $projectId, array $listFilter): TestResponse
{
    return test()->postJson('/api/tasks/search', [
        'query'   => '',
        'filters' => [
            [
                'filter_key' => 'lookup',
                'field_name' => 'project_id',
                'value'      => $projectId,
                'matchMode'  => 'equals',
                'params'     => [],
            ],
            $listFilter,
        ],
    ]);
}

it('excludes tasks that already belong to a list', function () {
    $response = searchCandidates($this->project->id, [
        'filter_key' => 'nullable',
        'field_name' => 'task_list_id',
        'value'      => null,
        'matchMode'  => 'equals',
        'params'     => [],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2)
        ->and(collect($response->json('data'))->pluck('id')->sort()->values()->all())
        ->toBe($this->unassigned->pluck('id')->sort()->values()->all());
});

// LookupFilter returns the query untouched when the value is empty, so expressing
// "has no list" through it silently yields every task of the project.
it('shows that the lookup filter cannot express "no list"', function () {
    $response = searchCandidates($this->project->id, [
        'filter_key' => 'lookup',
        'field_name' => 'task_list_id',
        'value'      => '',
        'matchMode'  => 'equals',
        'params'     => [],
    ]);

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(3);
});

it('rejects the nullable filter on a field that is not allow-listed', function () {
    $response = searchCandidates($this->project->id, [
        'filter_key' => 'nullable',
        'field_name' => 'description',
        'value'      => null,
        'matchMode'  => 'equals',
        'params'     => [],
    ]);

    $response->assertStatus(400);
});
