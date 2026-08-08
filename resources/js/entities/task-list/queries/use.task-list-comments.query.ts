import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { PagingParams } from '@/shared/types'
import { fetchTaskListCommentsRequest } from '../api'
import { TaskListCommentQueryKey } from '../config'

export function useTaskListCommentsQuery(
    taskListId: MaybeRefOrGetter<string>,
    pagination: MaybeRefOrGetter<PagingParams> = { page: 1, per_page: 20 }
) {
    const { data, isPending, isError, isFetching } = useQuery({
        queryKey: TaskListCommentQueryKey.taskListCommentsPaginated(taskListId, pagination),
        queryFn: () => {
            const { page, per_page } = toValue(pagination)
            return fetchTaskListCommentsRequest(toValue(taskListId), page, per_page)
        },
        placeholderData: keepPreviousData,
    })

    const comments = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { comments, paginationMeta, isPending, isError, isFetching }
}
