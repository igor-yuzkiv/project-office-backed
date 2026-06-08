import type { FilterDataType } from '../types/filter-def.types'
import type { MatchModeOption } from '../types/match-mode.types'
import {
    DATETIME_MATCH_MODES,
    INTEGER_MATCH_MODES,
    LOOKUP_MATCH_MODES,
    NULLABLE_MATCH_MODES,
    TEXT_MATCH_MODES,
} from '../types/match-mode.types'

export type FilterTypeConfig = {
    matchModes: MatchModeOption[]
    isEmpty: (value: unknown) => boolean
    omitValue?: boolean
    requiresMatchMode?: boolean
}

export const FILTER_TYPE_CONFIG: Record<FilterDataType, FilterTypeConfig> = {
    text: {
        matchModes: TEXT_MATCH_MODES,
        isEmpty: (v) => v === null || v === '',
    },
    integer: {
        matchModes: INTEGER_MATCH_MODES,
        isEmpty: (v) => v === null,
    },
    boolean: {
        matchModes: [],
        isEmpty: (v) => v === null,
    },
    datetime: {
        matchModes: DATETIME_MATCH_MODES,
        isEmpty: (v) => v === null,
    },
    nullable: {
        matchModes: NULLABLE_MATCH_MODES,
        isEmpty: () => false,
        omitValue: true,
        requiresMatchMode: true,
    },
    lookup: {
        matchModes: LOOKUP_MATCH_MODES,
        isEmpty: (v) => v === null || v === '',
    },
}

export const MATCH_MODE_OPTIONS: Record<FilterDataType, MatchModeOption[]> = Object.fromEntries(
    (Object.entries(FILTER_TYPE_CONFIG) as [FilterDataType, FilterTypeConfig][]).map(([k, v]) => [k, v.matchModes])
) as Record<FilterDataType, MatchModeOption[]>
