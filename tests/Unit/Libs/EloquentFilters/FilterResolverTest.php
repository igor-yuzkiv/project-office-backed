<?php

use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\FilterResolver;
use App\Libs\EloquentFilters\Filters\NullableFilter;
use App\Libs\EloquentFilters\Filters\TextFilter;
use App\Libs\EloquentFilters\InvalidFilterException;

$allowedFilters = [
    new FilterDefinition(TextFilter::class, ['name', 'prefix']),
    new FilterDefinition(NullableFilter::class, ['deleted_at']),
];

test('resolves a valid filter', function () use ($allowedFilters) {
    $resolver = new FilterResolver;

    $filter = $resolver->resolve([
        'filter_key' => 'text',
        'field_name' => 'name',
        'value'      => 'foo',
        'matchMode'  => 'contains',
        'params'     => [],
    ], $allowedFilters);

    expect($filter)->toBeInstanceOf(TextFilter::class);
});

test('throws on unknown filter key', function () use ($allowedFilters) {
    $resolver = new FilterResolver;

    expect(fn () => $resolver->resolve([
        'filter_key' => 'unknown',
        'field_name' => 'name',
        'value'      => 'foo',
        'matchMode'  => null,
        'params'     => [],
    ], $allowedFilters))->toThrow(InvalidFilterException::class);
});

test('throws on field not in allowed_fields', function () use ($allowedFilters) {
    $resolver = new FilterResolver;

    expect(fn () => $resolver->resolve([
        'filter_key' => 'text',
        'field_name' => 'password',
        'value'      => 'foo',
        'matchMode'  => null,
        'params'     => [],
    ], $allowedFilters))->toThrow(InvalidFilterException::class);
});

test('throws on unknown match mode string', function () use ($allowedFilters) {
    $resolver = new FilterResolver;

    expect(fn () => $resolver->resolve([
        'filter_key' => 'text',
        'field_name' => 'name',
        'value'      => 'foo',
        'matchMode'  => 'invalidMode',
        'params'     => [],
    ], $allowedFilters))->toThrow(InvalidFilterException::class);
});

test('throws on match mode unsupported by the filter', function () use ($allowedFilters) {
    $resolver = new FilterResolver;

    expect(fn () => $resolver->resolve([
        'filter_key' => 'text',
        'field_name' => 'name',
        'value'      => 'foo',
        'matchMode'  => 'gt',
        'params'     => [],
    ], $allowedFilters))->toThrow(InvalidFilterException::class);
});

test('accepts null match mode', function () use ($allowedFilters) {
    $resolver = new FilterResolver;

    $filter = $resolver->resolve([
        'filter_key' => 'text',
        'field_name' => 'name',
        'value'      => 'foo',
        'matchMode'  => null,
        'params'     => [],
    ], $allowedFilters);

    expect($filter)->toBeInstanceOf(TextFilter::class);
});
