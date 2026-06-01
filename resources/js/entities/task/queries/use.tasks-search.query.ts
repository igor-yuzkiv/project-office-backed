import { computed, type MaybeRef, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { TaskSearchParams } from '../types'
import { searchTasksRequest } from '../api'
import { TaskQueryKey } from '../config'

export function useTasksSearchQuery(params: MaybeRef<TaskSearchParams>) {
    const { data, isPending, isError } = useQuery({
        queryKey: TaskQueryKey.search(params),
        queryFn: () => searchTasksRequest(toValue(params)),
        placeholderData: keepPreviousData,
    })

    const tasks = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { tasks, paginationMeta, isPending, isError }
}
