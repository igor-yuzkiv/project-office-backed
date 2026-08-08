import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchTaskListRequest } from '../api'
import { TaskListQueryKey } from '../config'

export function useTaskListQuery(id: MaybeRefOrGetter<string>) {
    const { data, isPending, isError } = useQuery({
        queryKey: TaskListQueryKey.detail(id),
        queryFn: () => fetchTaskListRequest(toValue(id)),
    })

    const taskList = computed(() => data.value?.data)

    return { taskList, isPending, isError }
}
