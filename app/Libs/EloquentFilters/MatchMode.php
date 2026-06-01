<?php

namespace App\Libs\EloquentFilters;

enum MatchMode: string
{
    case STARTS_WITH = 'startsWith';
    case ENDS_WITH = 'endsWith';
    case CONTAINS = 'contains';
    case NOT_CONTAINS = 'notContains';
    case EQUALS = 'equals';
    case NOT_EQUALS = 'notEquals';
    case GREATER_THAN = 'gt';
    case GREATER_THAN_OR_EQUAL = 'gte';
    case LESS_THAN = 'lt';
    case LESS_THAN_OR_EQUAL = 'lte';
    case DATE_IS = 'dateIs';
    case DATE_IS_NOT = 'dateIsNot';
    case DATE_BEFORE = 'dateBefore';
    case DATE_AFTER = 'dateAfter';
}
