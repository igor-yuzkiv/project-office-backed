import { computed, ref, toValue, type MaybeRef } from 'vue'
import type { FilterPayloadItem } from '@/shared/filters'

export const DEFAULT_TASK_VIEW_KEY = 'all_open'

type TaskViewLike = {
    key: string
    label: string
    filters: FilterPayloadItem[]
}

export function useTaskViewSwitcher<TView extends TaskViewLike>(
    views: MaybeRef<readonly TView[]>,
    defaultKey: string = DEFAULT_TASK_VIEW_KEY
) {
    const activeViewKey = ref(defaultKey)

    const activeView = computed<TView | undefined>(() =>
        toValue(views).find((view) => view.key === activeViewKey.value)
    )

    const activeViewFilters = computed<FilterPayloadItem[]>(() => activeView.value?.filters ?? [])

    const activeViewLabel = computed(() => activeView.value?.label ?? '')

    function select(key: string): void {
        activeViewKey.value = key
    }

    return {
        activeViewKey,
        activeView,
        activeViewFilters,
        activeViewLabel,
        select,
    }
}
