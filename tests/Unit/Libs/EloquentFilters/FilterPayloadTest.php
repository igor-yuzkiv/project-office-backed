<?php

use App\Libs\EloquentFilters\FilterPayload;

test('toArray produces the request payload shape', function () {
    $payload = new FilterPayload(
        filterKey: 'text',
        fieldName: 'name',
        value: 'foo',
        matchMode: 'contains',
        params: ['relation' => 'project'],
    );

    expect($payload->toArray())->toBe([
        'filter_key' => 'text',
        'field_name' => 'name',
        'value'      => 'foo',
        'matchMode'  => 'contains',
        'params'     => ['relation' => 'project'],
    ]);
});

test('fromArray reverses toArray', function () {
    $payload = new FilterPayload(
        filterKey: 'nullable',
        fieldName: 'deleted_at',
        value: null,
        matchMode: 'equals',
        params: ['strict' => true],
    );

    expect(FilterPayload::fromArray($payload->toArray()))->toEqual($payload);
});

// The shape TaskViewRegistry actually produces: a list of statuses with no params. It is the one
// that travels to the Tasks page and back into the dashboard counts, so it is the one that has to
// survive the round trip intact — an array value flattened or cast on the way would put different
// numbers on Home and on Tasks.
test('fromArray reverses toArray for an array value with no params', function () {
    $payload = new FilterPayload(
        filterKey: 'text',
        fieldName: 'status',
        value: ['ready_for_development', 'in_progress', 'ready_to_test', 'completed'],
        matchMode: 'in',
        params: [],
    );

    expect(FilterPayload::fromArray($payload->toArray()))->toEqual($payload)
        ->and($payload->toArray()['value'])->toBe($payload->value);
});
