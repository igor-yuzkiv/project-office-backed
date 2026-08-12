import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import { fetchTaskRequest } from '../api'
import { TaskQueryKey } from '../config'

export function useTaskQuery(id: MaybeRefOrGetter<string>) {
    const { data, isPending, isError } = useQuery({
        queryKey: TaskQueryKey.detail(id),
        queryFn: () => fetchTaskRequest(toValue(id)),
        // The page hides itself while `task` is undefined, so without this the sidebar's own
        // Previous/Next buttons would blank the screen on every step through a list.
        placeholderData: keepPreviousData,
    })

    const task = computed(() => data.value?.data)

    return { task, isPending, isError }
}
