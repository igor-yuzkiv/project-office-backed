import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchTaskOwnersRequest } from '../api'
import { TaskOwnerQueryKey } from '../config'

export function useTaskOwnersQuery(taskId: MaybeRefOrGetter<string>) {
    const { data, isPending, isError } = useQuery({
        queryKey: TaskOwnerQueryKey.list(taskId),
        queryFn: () => fetchTaskOwnersRequest(toValue(taskId)),
    })

    const owners = computed(() => data.value?.data ?? [])

    return { owners, isPending, isError }
}
