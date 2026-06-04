import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchTaskRequest } from '../api'
import { TaskQueryKey } from '../config'

export function useTaskQuery(id: MaybeRefOrGetter<string>) {
    const { data, isPending, isError } = useQuery({
        queryKey: TaskQueryKey.detail(id),
        queryFn: () => fetchTaskRequest(toValue(id)),
    })

    const task = computed(() => data.value?.data)

    return { task, isPending, isError }
}
