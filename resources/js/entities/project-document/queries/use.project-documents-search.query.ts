import { computed, type MaybeRef, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { ProjectDocumentSearchParams } from '../types'
import { searchProjectDocumentsRequest } from '../api'
import { ProjectDocumentQueryKey } from '../config'

export function useProjectDocumentsSearchQuery(params: MaybeRef<ProjectDocumentSearchParams>) {
    const { data, isPending, isError } = useQuery({
        queryKey: ProjectDocumentQueryKey.search(params),
        queryFn: () => searchProjectDocumentsRequest(toValue(params)),
        placeholderData: keepPreviousData,
    })

    const projectDocuments = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { projectDocuments, paginationMeta, isPending, isError }
}
