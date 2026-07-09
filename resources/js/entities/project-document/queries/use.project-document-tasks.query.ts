import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { PagingParams } from '@/shared/types'
import { fetchProjectDocumentTasksRequest } from '../api'
import { ProjectDocumentTaskQueryKey } from '../config'

export function useProjectDocumentTasksQuery(
    documentId: MaybeRefOrGetter<string>,
    pagination: MaybeRefOrGetter<PagingParams> = { page: 1, per_page: 20 }
) {
    const { data, isPending, isError, isFetching } = useQuery({
        queryKey: ProjectDocumentTaskQueryKey.documentTasksPaginated(documentId, pagination),
        queryFn: () => {
            const { page, per_page } = toValue(pagination)
            return fetchProjectDocumentTasksRequest(toValue(documentId), page, per_page)
        },
        placeholderData: keepPreviousData,
    })

    const tasks = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { tasks, paginationMeta, isPending, isError, isFetching }
}
