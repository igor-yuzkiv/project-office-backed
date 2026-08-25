import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchProjectDocumentVersionAnnotationsRequest } from '../api'
import { ProjectDocumentVersionAnnotationQueryKey } from '../config'

export function useProjectDocumentVersionAnnotationsQuery(
    versionId: MaybeRefOrGetter<string>,
    options?: { enabled?: MaybeRefOrGetter<boolean> }
) {
    const { data, isPending, isError, isFetching, refetch } = useQuery({
        queryKey: ProjectDocumentVersionAnnotationQueryKey.versionAnnotations(versionId),
        queryFn: () => fetchProjectDocumentVersionAnnotationsRequest(toValue(versionId)),
        enabled: computed(() => (options?.enabled === undefined ? true : toValue(options.enabled))),
    })

    const annotations = computed(() => data.value?.data ?? [])

    return { annotations, isPending, isError, isFetching, refetch }
}
