<?php

use App\Libs\EloquentFilters\Filters\TextFilter;
use App\Libs\EloquentFilters\ParameterBag;
use Illuminate\Database\Eloquent\Builder;

function makeTextFilter(string $field, string $matchMode, string $value): TextFilter
{
    return new TextFilter(new ParameterBag([
        'field'     => $field,
        'matchMode' => $matchMode,
        'value'     => $value,
    ]));
}

test('contains match mode applies whereLike with surrounding wildcards', function () {
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('whereLike')->once()->with('name', '%foo%')->andReturnSelf();

    makeTextFilter('name', 'contains', 'foo')->apply($query);
});

test('startsWith match mode applies whereLike with trailing wildcard', function () {
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('whereLike')->once()->with('name', 'foo%')->andReturnSelf();

    makeTextFilter('name', 'startsWith', 'foo')->apply($query);
});

test('equals match mode applies exact where', function () {
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('where')->once()->with('name', 'foo')->andReturnSelf();

    makeTextFilter('name', 'equals', 'foo')->apply($query);
});

test('notContains match mode applies whereNotLike', function () {
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('whereNotLike')->once()->with('name', '%foo%')->andReturnSelf();

    makeTextFilter('name', 'notContains', 'foo')->apply($query);
});
