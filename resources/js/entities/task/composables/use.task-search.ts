import { computed, ref, toValue, watch, type MaybeRefOrGetter } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { PAGE_SIZE } from '@/app/config'
import { DEFAULT_TASK_VIEW_KEY, useTaskViewsQuery, useTaskViewSwitcher } from '@/entities/task-view'
import { usePersistedListState } from '@/shared/composables'
import { useFilterSidebar, type FilterPayloadItem } from '@/shared/filters'
import { useSortDialog } from '@/shared/sort'
import type { SortDirection } from '@/shared/sort'
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
    defaultTaskViewKey?: string
    /** Sorting a fresh visit starts with; a choice the browser remembered still wins over it. */
    defaultSort?: { field: string; order: SortDirection }
}

export function useTaskSearch(options: UseTaskSearchOptions = {}) {
    const route = useRoute()
    const router = useRouter()

    const filtersDefMap = createDefaultTaskFiltersDefMap()
    for (const field of options.hiddenFilterFields ?? []) {
        delete filtersDefMap[field]
    }

    const filterSidebar = useFilterSidebar(filtersDefMap)

    const { views: taskViews, isPending: isTaskViewsPending } = useTaskViewsQuery()
    const viewSwitcher = useTaskViewSwitcher(taskViews, options?.defaultTaskViewKey ?? DEFAULT_TASK_VIEW_KEY)

    const sort = useSortDialog(
        taskSortFieldDefs,
        options.defaultSort?.field ?? 'updated_at',
        options.defaultSort?.order ?? 'desc'
    )

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

    // A view asked for in the URL can only be checked against the registry once it has loaded, so
    // the search waits for that check as well: applying an unknown key and correcting it afterwards
    // would send a second search. Registered after usePersistedListState, so the restored filters
    // are already in place and can be cleared, and before the query, so both settle in one flush.
    const isUrlViewApplied = ref(false)

    watch(
        isTaskViewsPending,
        (isPending) => {
            if (isPending) return

            const requestedViewKey = route.query.view
            if (typeof requestedViewKey === 'string' && taskViews.value.some((view) => view.key === requestedViewKey)) {
                applyView(requestedViewKey)
            }

            isUrlViewApplied.value = true
        },
        { immediate: true }
    )

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
        enabled: computed(() => !isTaskViewsPending.value && isUrlViewApplied.value),
    })

    function submitSearch() {
        searchQuery.value = searchInput.value
        page.value = 1
    }

    /**
     * Arriving with ?view= is the same act as picking that view in the switcher, so it clears the
     * sidebar the same way. Otherwise a filter restored from the previous session narrows the list
     * silently, and a dashboard banner reading "All Closed 54" lands on far fewer rows.
     */
    function applyView(key: string) {
        viewSwitcher.select(key)
        filterSidebar.clear()
        page.value = 1
    }

    function selectView(key: string) {
        applyView(key)
        // replace, not push: otherwise the back button walks through the view switches instead of
        // leaving the page.
        void router.replace({ query: { ...route.query, view: key } })
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
