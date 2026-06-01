<?php

use App\Libs\EloquentFilters\Filters\NullableFilter;
use App\Libs\EloquentFilters\ParameterBag;
use Illuminate\Database\Eloquent\Builder;

function makeNullableFilter(string $field, string $matchMode): NullableFilter
{
    return new NullableFilter(new ParameterBag([
        'field'     => $field,
        'matchMode' => $matchMode,
        'value'     => null,
    ]));
}

test('equals match mode applies whereNull', function () {
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('whereNull')->once()->with('completed_at')->andReturnSelf();

    makeNullableFilter('completed_at', 'equals')->apply($query);
});

test('notEquals match mode applies whereNotNull', function () {
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('whereNotNull')->once()->with('completed_at')->andReturnSelf();

    makeNullableFilter('completed_at', 'notEquals')->apply($query);
});
