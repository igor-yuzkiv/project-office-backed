import type { FilterDataType } from '../types/filter-def.types'
import type { MatchModeOption } from '../types/match-mode.types'
import {
    DATETIME_MATCH_MODES,
    INTEGER_MATCH_MODES,
    LOOKUP_MATCH_MODES,
    NULLABLE_MATCH_MODES,
    TEXT_MATCH_MODES,
} from '../types/match-mode.types'

export const MATCH_MODE_OPTIONS: Record<FilterDataType, MatchModeOption[]> = {
    text: TEXT_MATCH_MODES,
    integer: INTEGER_MATCH_MODES,
    datetime: DATETIME_MATCH_MODES,
    nullable: NULLABLE_MATCH_MODES,
    boolean: [],
    lookup: LOOKUP_MATCH_MODES,
}
