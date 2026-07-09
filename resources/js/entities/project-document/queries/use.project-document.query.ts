import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchProjectDocumentRequest } from '../api'
import { ProjectDocumentQueryKey } from '../config'

export function useProjectDocumentQuery(id: MaybeRefOrGetter<string>) {
    const { data, isPending, isError } = useQuery({
        queryKey: ProjectDocumentQueryKey.detail(id),
        queryFn: () => fetchProjectDocumentRequest(toValue(id)),
    })

    const projectDocument = computed(() => data.value?.data)

    return { projectDocument, isPending, isError }
}
