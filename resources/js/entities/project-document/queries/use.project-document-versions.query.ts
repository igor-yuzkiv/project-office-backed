import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchProjectDocumentVersionsRequest } from '../api'
import { ProjectDocumentVersionQueryKey } from '../config'

export function useProjectDocumentVersionsQuery(documentId: MaybeRefOrGetter<string>) {
    const { data, isPending, isError, isFetching, refetch } = useQuery({
        queryKey: ProjectDocumentVersionQueryKey.documentVersions(documentId),
        queryFn: () => fetchProjectDocumentVersionsRequest(toValue(documentId)),
    })

    const versions = computed(() => data.value?.data ?? [])

    return { versions, isPending, isError, isFetching, refetch }
}
