import { computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchTaskViewsRequest } from '../api'
import { TaskViewQueryKey } from '../config'

export function useTaskViewsQuery() {
    const { data, isPending, isError } = useQuery({
        queryKey: TaskViewQueryKey.all,
        queryFn: () => fetchTaskViewsRequest(),
    })

    const views = computed(() => data.value ?? [])

    return { views, isPending, isError }
}
