export type MatchMode =
    | 'startsWith'
    | 'contains'
    | 'notContains'
    | 'endsWith'
    | 'equals'
    | 'notEquals'
    | 'gt'
    | 'gte'
    | 'lt'
    | 'lte'
    | 'dateIs'
    | 'dateIsNot'
    | 'dateBefore'
    | 'dateAfter'

export type MatchModeOption = {
    value: MatchMode
    label: string
}

export const TEXT_MATCH_MODES: MatchModeOption[] = [
    { value: 'contains', label: 'Contains' },
    { value: 'notContains', label: 'Not Contains' },
    { value: 'startsWith', label: 'Starts With' },
    { value: 'endsWith', label: 'Ends With' },
    { value: 'equals', label: 'Equals' },
    { value: 'notEquals', label: 'Not Equals' },
]

export const INTEGER_MATCH_MODES: MatchModeOption[] = [
    { value: 'equals', label: 'Equals' },
    { value: 'notEquals', label: 'Not Equals' },
    { value: 'gt', label: 'Greater Than' },
    { value: 'gte', label: 'Greater Than or Equal' },
    { value: 'lt', label: 'Less Than' },
    { value: 'lte', label: 'Less Than or Equal' },
]

export const DATETIME_MATCH_MODES: MatchModeOption[] = [
    { value: 'dateIs', label: 'Date Is' },
    { value: 'dateIsNot', label: 'Date Is Not' },
    { value: 'dateBefore', label: 'Date Before' },
    { value: 'dateAfter', label: 'Date After' },
    { value: 'equals', label: 'Equals' },
    { value: 'notEquals', label: 'Not Equals' },
]

export const NULLABLE_MATCH_MODES: MatchModeOption[] = [
    { value: 'equals', label: 'Is Empty' },
    { value: 'notEquals', label: 'Is Not Empty' },
]
