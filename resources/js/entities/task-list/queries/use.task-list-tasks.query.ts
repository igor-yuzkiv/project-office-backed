import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { PagingParams } from '@/shared/types'
import { fetchTaskListTasksRequest } from '../api'
import { TaskListQueryKey } from '../config'

export function useTaskListTasksQuery(
    taskListId: MaybeRefOrGetter<string>,
    pagination: MaybeRefOrGetter<PagingParams> = { page: 1, per_page: 100 },
    options?: { enabled?: MaybeRefOrGetter<boolean> }
) {
    const { data, isPending, isError } = useQuery({
        queryKey: TaskListQueryKey.tasks(taskListId, pagination),
        queryFn: () => {
            const { page, per_page } = toValue(pagination)
            return fetchTaskListTasksRequest(toValue(taskListId), page, per_page)
        },
        placeholderData: keepPreviousData,
        enabled: options?.enabled,
    })

    const tasks = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { tasks, paginationMeta, isPending, isError }
}
