import { computed, type MaybeRef, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import type { TaskListSearchParams } from '../types'
import { searchTaskListsRequest } from '../api'
import { TaskListQueryKey } from '../config'

export function useTaskListsSearchQuery(params: MaybeRef<TaskListSearchParams>) {
    const { data, isPending, isError } = useQuery({
        queryKey: TaskListQueryKey.search(params),
        queryFn: () => searchTaskListsRequest(toValue(params)),
    })

    const taskLists = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { taskLists, paginationMeta, isPending, isError }
}
