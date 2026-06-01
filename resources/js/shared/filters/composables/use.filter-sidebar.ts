import { ref, watch } from 'vue'
import type { AnyFilterDef, FilterDefMap } from '../types/filter-def.types'
import type { FilterPayloadItem } from '../types/filter-payload.types'
import { resolveFilters } from '../lib/filter-resolver'

function copyDefMap(source: FilterDefMap): FilterDefMap {
    return Object.fromEntries(Object.entries(source).map(([k, v]) => [k, { ...v }])) as FilterDefMap
}

export function useFilterSidebar(initialDefs: FilterDefMap) {
    const visible = ref(false)
    const committedDefMap = ref<FilterDefMap>(copyDefMap(initialDefs))
    const draftDefMap = ref<FilterDefMap>(copyDefMap(initialDefs))
    const resolvedFilters = ref<FilterPayloadItem[]>([])

    watch(visible, (open) => {
        if (open) draftDefMap.value = copyDefMap(committedDefMap.value)
    })

    function updateFilter(key: string, patch: Partial<AnyFilterDef>): void {
        if (!draftDefMap.value[key]) return
        draftDefMap.value[key] = { ...draftDefMap.value[key], ...patch } as AnyFilterDef
    }

    function apply(): void {
        committedDefMap.value = copyDefMap(draftDefMap.value)
        resolvedFilters.value = resolveFilters(committedDefMap.value)

        console.log('Applied Filters:', {
            resolvedFilters: resolvedFilters.value,
            committedDefMap: committedDefMap.value,
        })
    }

    function reset(): void {
        draftDefMap.value = copyDefMap(initialDefs)
    }

    return {
        visible,
        draftDefMap,
        resolvedFilters,
        updateFilter,
        apply,
        reset,
    }
}
