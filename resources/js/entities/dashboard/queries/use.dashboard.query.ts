import { computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchDashboardRequest } from '../api'
import { DashboardQueryKey } from '../config'

export function useDashboardQuery() {
    const { data, isPending, isError, isFetching, refetch } = useQuery({
        queryKey: DashboardQueryKey.all,
        queryFn: fetchDashboardRequest,
    })

    const recentTasks = computed(() => data.value?.data.recent_tasks ?? [])
    const recentTaskLists = computed(() => data.value?.data.recent_task_lists ?? [])

    return { recentTasks, recentTaskLists, isPending, isError, isFetching, refetch }
}
