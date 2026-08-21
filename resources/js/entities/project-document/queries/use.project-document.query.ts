import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchProjectDocumentRequest } from '../api'
import { ProjectDocumentQueryKey } from '../config'
import type { ProjectDocumentFetchParams } from '../types'

export function useProjectDocumentQuery(
    id: MaybeRefOrGetter<string>,
    params?: ProjectDocumentFetchParams,
    options?: { enabled?: MaybeRefOrGetter<boolean>; retry?: number | boolean }
) {
    const { data, isPending, isError, isFetching, refetch } = useQuery({
        queryKey: ProjectDocumentQueryKey.detail(id, params),
        queryFn: () => fetchProjectDocumentRequest(toValue(id), params),
        enabled: computed(() => (options?.enabled === undefined ? true : toValue(options.enabled))),
        ...(options?.retry === undefined ? {} : { retry: options.retry }),
    })

    const projectDocument = computed(() => data.value?.data)

    return { projectDocument, isPending, isError, isFetching, refetch }
}
