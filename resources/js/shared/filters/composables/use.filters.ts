import { computed, ref } from 'vue'
import type { AnyFilterDef, FilterDefMap } from '../types/filter-def.types'
import type { FilterPayloadItem } from '../types/filter-payload.types'
import { resolveFilters } from '../lib/filter-resolver'

export function useFilters(initialDefMap: FilterDefMap = {}) {
    const defMap = ref<FilterDefMap>({ ...initialDefMap })

    const resolvedFilters = computed<FilterPayloadItem[]>(() => resolveFilters(defMap.value))

    const hasActiveFilters = computed<boolean>(() => resolvedFilters.value.length > 0)

    function updateFilter(key: string, patch: Partial<AnyFilterDef>): void {
        if (!defMap.value[key]) return
        defMap.value[key] = { ...defMap.value[key], ...patch } as AnyFilterDef
    }

    function resetFilters(): void {
        defMap.value = Object.fromEntries(
            Object.entries(defMap.value).map(([key, def]) => [
                key,
                { ...def, value: def.defaultValue, matchMode: null, enabled: true },
            ])
        ) as FilterDefMap
    }

    return {
        defMap,
        resolvedFilters,
        hasActiveFilters,
        updateFilter,
        resetFilters,
    }
}
