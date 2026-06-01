import { computed, type MaybeRef, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { ProjectSearchParams } from '../types'
import { searchProjectsRequest } from '../api'
import { ProjectQueryKey } from '../config'

export function useProjectsSearchQuery(params: MaybeRef<ProjectSearchParams>) {
    const { data, isPending, isError } = useQuery({
        queryKey: ProjectQueryKey.search(params),
        queryFn: () => searchProjectsRequest(toValue(params)),
        placeholderData: keepPreviousData,
    })

    const projects = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { projects, paginationMeta, isPending, isError }
}
