import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchProjectDocumentAnnotationsRequest } from '../api'
import { ProjectDocumentAnnotationQueryKey } from '../config'

export function useProjectDocumentAnnotationsQuery(documentId: MaybeRefOrGetter<string>) {
    const { data, isPending, isError, isFetching, refetch } = useQuery({
        queryKey: ProjectDocumentAnnotationQueryKey.documentAnnotations(documentId),
        queryFn: () => fetchProjectDocumentAnnotationsRequest(toValue(documentId)),
    })

    const annotations = computed(() => data.value?.data ?? [])

    return { annotations, isPending, isError, isFetching, refetch }
}
