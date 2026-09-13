import { computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchPinnedProjectsRequest } from '../api'
import { ProjectQueryKey } from '../config'

export function usePinnedProjectsQuery() {
    const { data, isPending, isError } = useQuery({
        queryKey: ProjectQueryKey.pinned,
        queryFn: fetchPinnedProjectsRequest,
    })

    const projects = computed(() => data.value?.data ?? [])

    return { projects, isPending, isError }
}
