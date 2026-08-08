import { computed, ref, toValue, watch, type MaybeRefOrGetter } from 'vue'
import { PAGE_SIZE } from '@/app/config'
import { useTaskViewsQuery, useTaskViewSwitcher } from '@/entities/task-view'
import { usePersistedListState } from '@/shared/composables'
import { useFilterSidebar, type FilterPayloadItem } from '@/shared/filters'
import { useSortDialog } from '@/shared/sort'
import { createDefaultTaskFiltersDefMap, taskSortFieldDefs } from '../config'
import { useTasksSearchQuery } from '../queries'
import type { TaskInclude, TaskOverviewDto, TaskSearchParams } from '../types'

interface UseTaskSearchOptions {
    /**
     * A filter the page is scoped by, e.g. one project or one task list. It is applied on every
     * request and is deliberately kept out of the sidebar, so clearing filters cannot widen the
     * page beyond its own scope.
     */
    scopeFilter?: MaybeRefOrGetter<FilterPayloadItem | undefined>
    /** Filter fields the sidebar should not offer, typically the one the scope already fixes. */
    hiddenFilterFields?: string[]
    include?: TaskInclude[]
    /**
     * Storage key for filters and sort. Pages under a parameterised route need one, otherwise
     * every id gets its own entry keyed by path.
     */
    persistKey?: string
}

export function useTaskSearch(options: UseTaskSearchOptions = {}) {
    const filtersDefMap = createDefaultTaskFiltersDefMap()
    for (const field of options.hiddenFilterFields ?? []) {
        delete filtersDefMap[field]
    }

    const filterSidebar = useFilterSidebar(filtersDefMap)

    const { views: taskViews, isPending: isTaskViewsPending } = useTaskViewsQuery()
    const viewSwitcher = useTaskViewSwitcher(taskViews)

    const sort = useSortDialog(taskSortFieldDefs, 'updated_at', 'desc')

    usePersistedListState(
        {
            filters: filterSidebar.filtersSnapshot,
            sortBy: sort.sortBy,
            sortOrder: sort.sortOrder,
        },
        {
            key: options.persistKey,
            validate: (data) =>
                taskSortFieldDefs.some((f) => f.field === data.sortBy) &&
                (data.sortOrder === 'asc' || data.sortOrder === 'desc'),
        }
    )

    const searchInput = ref('')
    const searchQuery = ref('')
    const page = ref(1)

    const searchParams = computed<TaskSearchParams>(() => {
        const scopeFilter = toValue(options.scopeFilter)

        return {
            query: searchQuery.value,
            include: options.include,
            filters: [
                ...(scopeFilter ? [scopeFilter] : []),
                ...viewSwitcher.activeViewFilters.value,
                ...filterSidebar.resolvedFilters.value,
            ],
            page: page.value,
            per_page: PAGE_SIZE,
            sort_by: sort.sortBy.value,
            sort_order: sort.sortOrder.value,
        }
    })

    // Gate the search until the task views are settled, otherwise it fires once with no view
    // filters and again once the default view (All Open) loads.
    const { tasks, paginationMeta, isPending } = useTasksSearchQuery(searchParams, {
        enabled: computed(() => !isTaskViewsPending.value),
    })

    function submitSearch() {
        searchQuery.value = searchInput.value
        page.value = 1
    }

    function selectView(key: string) {
        viewSwitcher.select(key)
        filterSidebar.clear()
        page.value = 1
    }

    function applySort() {
        sort.apply()
        sort.close()
    }

    function goToPage(newPage: number) {
        page.value = newPage
    }

    function taskDetailsRoute(task: TaskOverviewDto) {
        return { name: 'task-details', params: { id: task.id } }
    }

    watch([sort.sortBy, sort.sortOrder], () => {
        page.value = 1
    })

    return {
        searchInput,
        page,
        searchParams,
        tasks,
        paginationMeta,
        isPending,
        filterSidebar,
        sort,
        taskViews,
        viewSwitcher,
        submitSearch,
        selectView,
        applySort,
        goToPage,
        taskDetailsRoute,
    }
}
