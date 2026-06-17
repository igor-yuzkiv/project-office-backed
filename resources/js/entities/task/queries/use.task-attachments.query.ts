import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { PagingParams } from '@/shared/types'
import { fetchTaskAttachmentsRequest } from '../api/task-attachments.api'
import { TaskAttachmentQueryKey } from '../config'

export function useTaskAttachmentsQuery(
    taskId: MaybeRefOrGetter<string>,
    pagination: MaybeRefOrGetter<PagingParams> = { page: 1, per_page: 50 }
) {
    const { data, isPending, isError, isFetching } = useQuery({
        queryKey: TaskAttachmentQueryKey.taskAttachmentsPaginated(taskId, pagination),
        queryFn: () => {
            const { page, per_page } = toValue(pagination)
            return fetchTaskAttachmentsRequest(toValue(taskId), page, per_page)
        },
        placeholderData: keepPreviousData,
    })

    const attachments = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { attachments, paginationMeta, isPending, isError, isFetching }
}
