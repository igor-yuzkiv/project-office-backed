import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { PagingParams } from '@/shared/types'
import { fetchTaskListAttachmentsRequest } from '../api'
import { TaskListAttachmentQueryKey } from '../config'

export function useTaskListAttachmentsQuery(
    taskListId: MaybeRefOrGetter<string>,
    pagination: MaybeRefOrGetter<PagingParams> = { page: 1, per_page: 50 }
) {
    const { data, isPending, isError, isFetching } = useQuery({
        queryKey: TaskListAttachmentQueryKey.taskListAttachmentsPaginated(taskListId, pagination),
        queryFn: () => {
            const { page, per_page } = toValue(pagination)
            return fetchTaskListAttachmentsRequest(toValue(taskListId), page, per_page)
        },
        placeholderData: keepPreviousData,
    })

    const attachments = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { attachments, paginationMeta, isPending, isError, isFetching }
}
