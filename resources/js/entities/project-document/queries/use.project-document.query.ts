import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchProjectDocumentRequest } from '../api'
import { ProjectDocumentQueryKey } from '../config'
import type { ProjectDocumentFetchParams } from '../types'

export function useProjectDocumentQuery(id: MaybeRefOrGetter<string>, params?: ProjectDocumentFetchParams) {
    const { data, isPending, isError } = useQuery({
        queryKey: ProjectDocumentQueryKey.detail(id, params),
        queryFn: () => fetchProjectDocumentRequest(toValue(id), params),
    })

    const projectDocument = computed(() => data.value?.data)

    return { projectDocument, isPending, isError }
}
