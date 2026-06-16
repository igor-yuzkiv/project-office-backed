import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { PagingParams } from '@/shared/types'
import { fetchTaskCommentsRequest } from '../api'
import { CommentQueryKey } from '../config'

export function useTaskCommentsQuery(
    taskId: MaybeRefOrGetter<string>,
    pagination: MaybeRefOrGetter<PagingParams> = { page: 1, per_page: 20 }
) {
    const { data, isPending, isError, isFetching } = useQuery({
        queryKey: CommentQueryKey.taskCommentsPaginated(taskId, pagination),
        queryFn: () => {
            const { page, per_page } = toValue(pagination)
            return fetchTaskCommentsRequest(toValue(taskId), page, per_page)
        },
        placeholderData: keepPreviousData,
    })

    const comments = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { comments, paginationMeta, isPending, isError, isFetching }
}
