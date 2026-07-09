import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { PagingParams } from '@/shared/types'
import { fetchTaskProjectDocumentsRequest } from '../api'
import { TaskProjectDocumentQueryKey } from '../config'

export function useTaskProjectDocumentsQuery(
    taskId: MaybeRefOrGetter<string>,
    pagination: MaybeRefOrGetter<PagingParams> = { page: 1, per_page: 20 }
) {
    const { data, isPending, isError, isFetching } = useQuery({
        queryKey: TaskProjectDocumentQueryKey.taskProjectDocumentsPaginated(taskId, pagination),
        queryFn: () => {
            const { page, per_page } = toValue(pagination)
            return fetchTaskProjectDocumentsRequest(toValue(taskId), page, per_page)
        },
        placeholderData: keepPreviousData,
    })

    const projectDocuments = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { projectDocuments, paginationMeta, isPending, isError, isFetching }
}
