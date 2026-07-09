import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { PagingParams } from '@/shared/types'
import { fetchProjectDocumentTreeRequest } from '../api'
import { ProjectDocumentQueryKey } from '../config'

export function useProjectDocumentChildrenQuery(
    projectId: MaybeRefOrGetter<string>,
    parentId: MaybeRefOrGetter<string>,
    pagination: MaybeRefOrGetter<PagingParams> = { page: 1, per_page: 20 }
) {
    const params = computed(() => ({ parent_id: toValue(parentId), ...toValue(pagination) }))

    const { data, isPending, isError } = useQuery({
        queryKey: ProjectDocumentQueryKey.tree(projectId, params),
        queryFn: () => fetchProjectDocumentTreeRequest(toValue(projectId), params.value),
        enabled: computed(() => !!toValue(projectId)),
        placeholderData: keepPreviousData,
    })

    const children = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { children, paginationMeta, isPending, isError }
}
