import { computed, type MaybeRef, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { PagingParams } from '@/shared/types'
import { fetchProjectsRequest } from '../api'
import { ProjectQueryKey } from '../config'

export function useProjectsQuery(pagination: MaybeRef<PagingParams> = { page: 1, per_page: 20 }) {
    const { data, isPending, isError, refetch } = useQuery({
        queryKey: ProjectQueryKey.paginated(pagination),
        queryFn: () => fetchProjectsRequest(toValue(pagination)),
        placeholderData: keepPreviousData,
    })

    const projects = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return {
        projects,
        paginationMeta,
        isPending,
        isError,
        refetch,
    }
}
