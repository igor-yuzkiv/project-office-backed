import { computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchDashboardRequest } from '../api'
import { DashboardQueryKey } from '../config'

export function useDashboardQuery() {
    const { data, isPending, isError, isFetching, refetch } = useQuery({
        queryKey: DashboardQueryKey.all,
        queryFn: fetchDashboardRequest,
    })

    const dashboard = computed(() => data.value?.data)
    const summary = computed(() => dashboard.value?.summary)
    const recentTasks = computed(() => dashboard.value?.recent_tasks ?? [])
    const recentTaskLists = computed(() => dashboard.value?.recent_task_lists ?? [])

    return { dashboard, summary, recentTasks, recentTaskLists, isPending, isError, isFetching, refetch }
}
