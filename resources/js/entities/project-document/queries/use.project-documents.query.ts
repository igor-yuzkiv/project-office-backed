import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchProjectDocumentsRequest } from '../api'
import { ProjectDocumentQueryKey } from '../config'
import type { ProjectDocumentFetchParams } from '../types'

export function useProjectDocumentsQuery(
    projectId: MaybeRefOrGetter<string>,
    params?: MaybeRefOrGetter<ProjectDocumentFetchParams>
) {
    const { data, isPending, isError } = useQuery({
        queryKey: ProjectDocumentQueryKey.list(projectId, params),
        queryFn: () => fetchProjectDocumentsRequest(toValue(projectId), toValue(params)),
    })

    const projectDocuments = computed(() => data.value?.data ?? [])

    return { projectDocuments, isPending, isError }
}
