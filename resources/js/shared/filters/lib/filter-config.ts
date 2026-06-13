import type { FilterDataType, FilterFieldTypeConfig } from '../types/filter-def.types'
import {
    DATETIME_MATCH_MODES,
    INTEGER_MATCH_MODES,
    LOOKUP_MATCH_MODES,
    NULLABLE_MATCH_MODES,
    SELECT_MATCH_MODES,
    TEXT_MATCH_MODES,
} from '../types/match-mode.types'

import TextInput from '../ui/value-inputs/TextInput.vue'
import IntegerInput from '../ui/value-inputs/IntegerInput.vue'
import BooleanInput from '../ui/value-inputs/BooleanInput.vue'
import DateTimeInput from '../ui/value-inputs/DateTimeInput.vue'
import SelectInput from '../ui/value-inputs/SelectInput.vue'
import { markRaw } from 'vue'
import { TagFilterInput } from '@/widgets/tags/filters'

export const FilterFieldTypeConfigMap: Record<FilterDataType, FilterFieldTypeConfig> = {
    text: {
        matchModes: TEXT_MATCH_MODES,
        isInputValueEmpty: (v) => v === null || v === '',
        component: markRaw(TextInput),
    },
    integer: {
        matchModes: INTEGER_MATCH_MODES,
        isInputValueEmpty: (v) => v === null,
        component: markRaw(IntegerInput),
    },
    boolean: {
        matchModes: [],
        isInputValueEmpty: (v) => v === null,
        component: markRaw(BooleanInput),
    },
    datetime: {
        matchModes: DATETIME_MATCH_MODES,
        isInputValueEmpty: (v) => v === null,
        component: markRaw(DateTimeInput),
    },
    nullable: {
        matchModes: NULLABLE_MATCH_MODES,
        isInputValueEmpty: () => false,
        omitValue: true,
        requiresMatchMode: true,
        component: null,
    },
    lookup: {
        matchModes: LOOKUP_MATCH_MODES,
        isInputValueEmpty: (v) => v === null || v === '',
        component: null,
    },
    select: {
        matchModes: SELECT_MATCH_MODES,
        isInputValueEmpty: (v) => !Array.isArray(v) || v.length === 0,
        requiresMatchMode: true,
        filterKey: 'text',
        component: markRaw(SelectInput),
    },
    tags: {
        matchModes: [],
        isInputValueEmpty: (v) => !Array.isArray(v) || v.length === 0,
        component: markRaw(TagFilterInput),
    },
}
