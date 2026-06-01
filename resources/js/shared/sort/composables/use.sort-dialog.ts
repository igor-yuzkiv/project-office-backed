import { computed, ref } from 'vue'
import type { SortDirection, SortFieldDef } from '../types/sort.types'

export function useSortDialog(fields: SortFieldDef[], defaultField?: string, defaultOrder: SortDirection = 'asc') {
    const resolvedDefault = defaultField ?? fields[0]?.field ?? ''

    const visible = ref(false)

    const sortBy = ref(resolvedDefault)
    const sortOrder = ref<SortDirection>(defaultOrder)

    const draftSortBy = ref(resolvedDefault)
    const draftSortOrder = ref<SortDirection>(defaultOrder)

    const activeSortLabel = computed(() => fields.find((f) => f.field === sortBy.value)?.label ?? '')

    function open(): void {
        draftSortBy.value = sortBy.value
        draftSortOrder.value = sortOrder.value
        visible.value = true
    }

    function close(): void {
        visible.value = false
    }

    function setDraftField(field: string): void {
        draftSortBy.value = field
    }

    function setDraftOrder(order: SortDirection): void {
        draftSortOrder.value = order
    }

    function apply(): void {
        sortBy.value = draftSortBy.value
        sortOrder.value = draftSortOrder.value
    }

    function reset(): void {
        draftSortBy.value = resolvedDefault
        draftSortOrder.value = defaultOrder
    }

    return {
        visible,
        sortBy,
        sortOrder,
        activeSortLabel,
        draftSortBy,
        draftSortOrder,
        open,
        close,
        setDraftField,
        setDraftOrder,
        apply,
        reset,
    }
}
