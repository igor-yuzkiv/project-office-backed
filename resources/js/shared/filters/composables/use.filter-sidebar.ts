import { computed, ref, watch } from 'vue'
import type { AnyFilterDef, FilterDefMap } from '../types/filter-def.types'
import type { FilterPayloadItem } from '../types/filter-payload.types'
import { resolveFilters } from '../lib/filter-resolver'

function copyDefMap(source: FilterDefMap): FilterDefMap {
    return Object.fromEntries(Object.entries(source).map(([k, v]) => [k, { ...v }])) as FilterDefMap
}

type FilterStateSnapshot = Record<string, { value: unknown; matchMode: string | null; enabled: boolean }>

// JSON round-trips a Date as an ISO string; revive it so a restored datetime filter
// still holds a Date, matching what the field's dataType expects.
function reviveFilterValue(def: AnyFilterDef, value: unknown): unknown {
    return def.dataType === 'datetime' && typeof value === 'string' ? new Date(value) : value
}

export function useFilterSidebar(initialDefs: FilterDefMap) {
    const visible = ref(false)
    const committedDefMap = ref<FilterDefMap>(copyDefMap(initialDefs))
    const draftDefMap = ref<FilterDefMap>(copyDefMap(initialDefs))

    const resolvedFilters = computed<FilterPayloadItem[]>(() => resolveFilters(committedDefMap.value))

    watch(visible, (open) => {
        if (open) draftDefMap.value = copyDefMap(committedDefMap.value)
    })

    function updateFilter(key: string, patch: Partial<AnyFilterDef>): void {
        if (!draftDefMap.value[key]) return
        draftDefMap.value[key] = { ...draftDefMap.value[key], ...patch } as AnyFilterDef
    }

    function apply(): void {
        committedDefMap.value = copyDefMap(draftDefMap.value)
    }

    function reset(): void {
        draftDefMap.value = copyDefMap(initialDefs)
    }

    function clear(): void {
        committedDefMap.value = copyDefMap(initialDefs)
        draftDefMap.value = copyDefMap(initialDefs)
    }

    // Serializable projection of the committed filters (no components, no non-JSON values),
    // for persisting/restoring state (e.g. usePersistedListState) without touching field defs.
    const filtersSnapshot = computed<FilterStateSnapshot>({
        get: () =>
            Object.fromEntries(
                Object.entries(committedDefMap.value).map(([key, def]) => [
                    key,
                    { value: def.value, matchMode: def.matchMode, enabled: def.enabled },
                ])
            ),
        set: (snapshot) => {
            committedDefMap.value = Object.fromEntries(
                Object.entries(committedDefMap.value).map(([key, def]) => {
                    const patch = snapshot[key]
                    if (!patch) return [key, def]
                    return [key, { ...def, ...patch, value: reviveFilterValue(def, patch.value) }]
                })
            ) as FilterDefMap
            draftDefMap.value = copyDefMap(committedDefMap.value)
        },
    })

    const sidebarProps = computed(() => ({
        visible: visible.value,
        'onUpdate:visible': (v: boolean) => {
            visible.value = v
        },
        defMap: draftDefMap.value,
        onChange: updateFilter,
        onApply: apply,
        onReset: reset,
    }))

    const buttonProps = computed(() => ({
        count: resolvedFilters.value.length,
        onClick: () => {
            visible.value = true
        },
    }))

    return {
        visible,
        draftDefMap,
        resolvedFilters,
        filtersSnapshot,
        updateFilter,
        apply,
        reset,
        clear,
        sidebarProps,
        buttonProps,
    }
}
